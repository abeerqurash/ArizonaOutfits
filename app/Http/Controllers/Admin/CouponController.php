<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends AdminController
{
    private const TYPES = ['fixed', 'percentage'];
    private const TARGET_TYPES = ['all', 'products', 'categories', 'variants'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'scheduled', 'expired', 'exhausted'])],
            'type' => ['nullable', Rule::in(self::TYPES)],
        ]);

        $now = now();

        $coupons = Coupon::query()
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('code', 'like', '%' . $search . '%')
                        ->orWhere('event_name', 'like', '%' . $search . '%');
                });
            })
            ->when(filled($filters['type'] ?? null), fn ($query) => $query->where('type', $filters['type']))
            ->when(filled($filters['status'] ?? null), function ($query) use ($filters, $now) {
                match ($filters['status']) {
                    'inactive' => $query->where('status', false),
                    'scheduled' => $query->where('status', true)->whereNotNull('start_date')->where('start_date', '>', $now),
                    'expired' => $query->where('status', true)->whereNotNull('end_date')->where('end_date', '<', $now),
                    'exhausted' => $query->where('status', true)->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit'),
                    'active' => $query
                        ->where('status', true)
                        ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $now))
                        ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $now))
                        ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit')),
                    default => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalCoupons = Coupon::query()->count();
        $activeCoupons = Coupon::query()
            ->where('status', true)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $now))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->count();
        $scheduledCoupons = Coupon::query()->where('status', true)->where('start_date', '>', $now)->count();
        $totalRedemptions = (int) Coupon::query()->sum('used_count');

        return view('admin.coupons.index', compact(
            'coupons',
            'totalCoupons',
            'activeCoupons',
            'scheduledCoupons',
            'totalRedemptions'
        ));
    }

    public function create(): View
    {
        return view('admin.coupons.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedCoupon($request);
        $data['code'] = $this->normalizeCode($data['code']);
        $this->ensureUniqueCode($data['code']);

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', array_merge(
            $this->formData(),
            compact('coupon')
        ));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $this->validatedCoupon($request);
        $data['code'] = $this->normalizeCode($data['code']);
        $this->ensureUniqueCode($data['code'], $coupon->id);

        $coupon->update($data);

        $appliedCoupon = session('cart_coupon');

        if (
            is_array($appliedCoupon)
            && (int) ($appliedCoupon['id'] ?? 0) === (int) $coupon->id
        ) {
            session()->forget('cart_coupon');
        }

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $appliedCoupon = session('cart_coupon');

        if (
            is_array($appliedCoupon)
            && (int) ($appliedCoupon['id'] ?? 0) === (int) $coupon->id
        ) {
            session()->forget('cart_coupon');
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    private function validatedCoupon(Request $request): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(self::TYPES)],
            'value' => ['required', 'numeric', 'gt:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'gt:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'target_type' => ['required', Rule::in(self::TARGET_TYPES)],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:product_categories,id'],
            'variant_ids' => ['nullable', 'array'],
            'variant_ids.*' => ['integer', 'exists:product_variants,id'],
            'include_child_categories' => ['nullable', 'boolean'],
            'event_name' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'boolean'],
        ]);

        if (($data['type'] ?? null) === 'percentage' && (float) $data['value'] > 100) {
            validator(
                ['value' => $data['value']],
                ['value' => ['numeric', 'max:100']],
                ['value.max' => 'Percentage coupons cannot exceed 100%.']
            )->validate();
        }

        $target = $data['target_type'];

        if ($target === 'products' && empty($data['product_ids'])) {
            validator([], ['product_ids' => ['required']], ['product_ids.required' => 'Select at least one product.'])->validate();
        }

        if ($target === 'categories' && empty($data['category_ids'])) {
            validator([], ['category_ids' => ['required']], ['category_ids.required' => 'Select at least one category.'])->validate();
        }

        if ($target === 'variants' && empty($data['variant_ids'])) {
            validator([], ['variant_ids' => ['required']], ['variant_ids.required' => 'Select at least one product variation.'])->validate();
        }

        $data['minimum_order_amount'] = $data['minimum_order_amount'] ?? 0;
        $data['maximum_discount'] = $data['maximum_discount'] ?? null;
        $data['usage_limit'] = $data['usage_limit'] ?? null;
        $data['per_user_usage_limit'] = $data['per_user_usage_limit'] ?? null;
        $data['start_date'] = $data['start_date'] ?? null;
        $data['end_date'] = $data['end_date'] ?? null;
        $data['status'] = $request->boolean('status');
        $data['include_child_categories'] = $request->boolean('include_child_categories');

        $data['product_ids'] = $target === 'products'
            ? array_values(array_unique(array_map('intval', $data['product_ids'] ?? [])))
            : null;
        $data['category_ids'] = $target === 'categories'
            ? array_values(array_unique(array_map('intval', $data['category_ids'] ?? [])))
            : null;
        $data['variant_ids'] = $target === 'variants'
            ? array_values(array_unique(array_map('intval', $data['variant_ids'] ?? [])))
            : null;

        return $data;
    }

    private function formData(): array
    {
        return [
            'products' => Product::query()
                ->select(['id', 'title', 'sku'])
                ->orderBy('title')
                ->get(),
            'categories' => ProductCategory::query()
                ->select(['id', 'title', 'parent_id'])
                ->orderBy('title')
                ->get(),
            'variants' => ProductVariant::query()
                ->with('product:id,title')
                ->select(['id', 'product_id', 'sku', 'options'])
                ->orderBy('product_id')
                ->orderBy('id')
                ->get(),
        ];
    }

    private function normalizeCode(string $code): string
    {
        $normalized = strtoupper(Str::slug(trim($code), ''));

        return $normalized !== '' ? $normalized : strtoupper(Str::random(10));
    }

    private function ensureUniqueCode(string $code, ?int $ignoreId = null): void
    {
        validator(
            ['code' => $code],
            [
                'code' => [
                    Rule::unique('coupons', 'code')->ignore($ignoreId),
                ],
            ],
            ['code.unique' => 'This coupon code is already in use.']
        )->validate();
    }
}
