<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProductCategoryController extends AdminController
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'parent' => ['nullable', 'integer', 'exists:product_categories,id'],
        ]);

        $categories = ProductCategory::query()
            ->with('parent')
            ->withCount(['products', 'children'])
            ->when(
                filled($validated['search'] ?? null),
                function ($query) use ($validated) {
                    $search = trim((string) $validated['search']);

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('title', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }
            )
            ->when(
                array_key_exists('parent', $validated),
                function ($query) use ($validated) {
                    if ($validated['parent'] === null) {
                        return;
                    }

                    $query->where('parent_id', $validated['parent']);
                }
            )
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $parents = ProductCategory::query()
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title']);

        $stats = [
            'total' => ProductCategory::count(),
            'parents' => ProductCategory::whereNull('parent_id')->count(),
            'children' => ProductCategory::whereNotNull('parent_id')->count(),
            'with_products' => ProductCategory::whereHas('products')->count(),
        ];

        return view(
            'admin.product-categories.index',
            compact('categories', 'parents', 'stats')
        );
    }

    public function create(): View
    {
        $parents = ProductCategory::query()
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view(
            'admin.product-categories.create',
            compact('parents')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);
        $newImage = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->storeSeoImage(
                    $request->file('image'),
                    'product-categories'
                );
            }

            ProductCategory::create(
                $this->categoryPayload(
                    validated: $validated,
                    featuredImage: $newImage
                )
            );

            DB::commit();

            return redirect()
                ->route('admin.product-categories.index')
                ->with('success', 'Product category created successfully.');
        } catch (Throwable $exception) {
            $this->safeRollback();

            if ($newImage) {
                $this->deleteImage($newImage);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The product category could not be created: ' .
                    $exception->getMessage()
                );
        }
    }

    public function edit(ProductCategory $productCategory): View
    {
        $productCategory->loadCount(['products', 'children']);

        $parents = ProductCategory::query()
            ->whereNull('parent_id')
            ->whereKeyNot($productCategory->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view(
            'admin.product-categories.edit',
            compact('productCategory', 'parents')
        );
    }

    public function update(
        Request $request,
        ProductCategory $productCategory
    ): RedirectResponse {
        $validated = $this->validateCategory(
            request: $request,
            productCategory: $productCategory
        );

        $oldImage = $productCategory->featured_image;
        $featuredImage = $oldImage;
        $newImage = null;
        $deleteOldImageAfterCommit = false;

        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->storeSeoImage(
                    $request->file('image'),
                    'product-categories'
                );

                $featuredImage = $newImage;
                $deleteOldImageAfterCommit = (bool) $oldImage;
            } elseif ($request->boolean('remove_image')) {
                $featuredImage = null;
                $deleteOldImageAfterCommit = (bool) $oldImage;
            }

            $productCategory->update(
                $this->categoryPayload(
                    validated: $validated,
                    featuredImage: $featuredImage
                )
            );

            DB::commit();

            if (
                $deleteOldImageAfterCommit &&
                $oldImage &&
                $oldImage !== $newImage
            ) {
                $this->deleteImage($oldImage);
            }

            return redirect()
                ->route('admin.product-categories.index')
                ->with('success', 'Product category updated successfully.');
        } catch (Throwable $exception) {
            $this->safeRollback();

            if ($newImage) {
                $this->deleteImage($newImage);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The product category could not be updated: ' .
                    $exception->getMessage()
                );
        }
    }

    public function destroy(
        ProductCategory $productCategory
    ): RedirectResponse {
        $productCategory->loadCount(['products', 'children']);

        if ($productCategory->products_count > 0) {
            return back()->with(
                'error',
                'This category cannot be deleted because products are assigned to it. Move or remove those products first.'
            );
        }

        if ($productCategory->children_count > 0) {
            return back()->with(
                'error',
                'This category cannot be deleted because it has child categories. Move or delete the child categories first.'
            );
        }

        $oldImage = $productCategory->featured_image;

        DB::beginTransaction();

        try {
            $productCategory->delete();

            DB::commit();

            if ($oldImage) {
                $this->deleteImage($oldImage);
            }

            return redirect()
                ->route('admin.product-categories.index')
                ->with('success', 'Product category deleted successfully.');
        } catch (Throwable $exception) {
            $this->safeRollback();

            report($exception);

            return back()->with(
                'error',
                'The product category could not be deleted: ' .
                $exception->getMessage()
            );
        }
    }

    private function validateCategory(
        Request $request,
        ?ProductCategory $productCategory = null
    ): array {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id'),
                function ($attribute, $value, $fail) use ($productCategory) {
                    if (
                        $productCategory &&
                        (int) $value === (int) $productCategory->id
                    ) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_image' => [
                'nullable',
                'boolean',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $validated['slug'] = $this->uniqueSlug(
            requestedSlug: $validated['slug'] ?? null,
            title: $validated['title'],
            ignoreId: $productCategory?->id
        );

        return $validated;
    }

    private function categoryPayload(
        array $validated,
        ?string $featuredImage
    ): array {
        return [
            'title' => trim($validated['title']),
            'slug' => $validated['slug'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $this->nullableString(
                $validated['description'] ?? null
            ),
            'featured_image' => $featuredImage,
            'meta_title' => $this->nullableString(
                $validated['meta_title'] ?? null
            ),
            'meta_description' => $this->nullableString(
                $validated['meta_description'] ?? null
            ),
            'meta_keywords' => $this->nullableString(
                $validated['meta_keywords'] ?? null
            ),
        ];
    }

    private function uniqueSlug(
        ?string $requestedSlug,
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug(
            filled($requestedSlug)
                ? $requestedSlug
                : $title
        );

        if ($base === '') {
            $base = 'category';
        }

        $slug = $base;
        $counter = 2;

        while (
            ProductCategory::query()
                ->when(
                    $ignoreId,
                    fn ($query) => $query->whereKeyNot($ignoreId)
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeSeoImage(
        UploadedFile $file,
        string $directory
    ): string {
        $originalName = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $baseName = Str::slug($originalName);

        if ($baseName === '') {
            $baseName = 'image';
        }

        $fileName = $baseName . '.' . $extension;
        $counter = 2;

        while (
            Storage::disk('public')->exists(
                $directory . '/' . $fileName
            )
        ) {
            $fileName =
                $baseName .
                '-' .
                $counter .
                '.' .
                $extension;

            $counter++;
        }

        return $file->storeAs(
            $directory,
            $fileName,
            'public'
        );
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $path = ltrim(
            str_replace('\\', '/', $path),
            '/'
        );

        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        Storage::disk('public')->delete($path);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }

    private function safeRollback(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    }
}
