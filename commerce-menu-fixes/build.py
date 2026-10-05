from pathlib import Path
import re, json, hashlib
ROOT=Path(r'C:\xampp\htdocs\ArizonaOutfits')
OUT=ROOT/'commerce-menu-fixes'
STAGE=OUT/'_staged'
for old in OUT.glob('*.txt'):
    if re.fullmatch(r'\d{2}-(?:(?:app|routes|resources)--.*|[A-Za-z][A-Za-z0-9.-]*)\.txt', old.name):
        assert old.resolve().parent == OUT.resolve()
        old.unlink()
manifest=[]
def read(path):
    return (ROOT/path).read_text(encoding='utf-8-sig').replace('\r\n','\n')
def save(path,text):
    dest=STAGE/path;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_text(text,encoding='utf-8')
    label={'routes/web.php':'web-routes',
        'resources/views/admin/partials/sidebar.blade.php':'admin-sidebar',
        'resources/views/admin/payment-verifications/show.blade.php':'payment-verification-view'}.get(path,Path(path).stem)
    name=f'{len(manifest)+1:02d}-'+label+'.txt'
    (OUT/name).write_text(text,encoding='utf-8')
    manifest.append({'download':name,'destination':str(ROOT/path),'source_sha256':hashlib.sha256((ROOT/path).read_bytes()).hexdigest()})
def compact(text):return re.sub(r'\n(?:[ \t]*\n){2,}', '\n\n', text)
def sub(text,pattern,replacement,count=1):
    result,n=re.subn(pattern,replacement,text,count=count,flags=re.S)
    assert n==count,(pattern,n,count)
    return result

# Customer history, dashboard statistics and invoices include archived orders,
# always through the authenticated customer's relationship.
p='app/Http/Controllers/CustomerDashboardController.php';s=read(p)
s=s.replace('->orders()', '->orders()->withTrashed()');save(p,s)

# Durable payment lookup, proper dependency injection and idempotent success.
p='app/Http/Controllers/Webhooks/StripeWebhookController.php';s=compact(read(p))
s=s.replace('private readonly OrderEmailService $orderEmailService','private readonly OrderEmailService $orderEmailService,\n        private readonly CouponRedemptionService $couponRedemptionService')
s=s.replace('Order::query()', 'Order::withTrashed()')
s=s.replace("if ($order->payment_status === 'paid')", "if (in_array($order->payment_status, ['paid', 'refunded'], true)\n                || in_array($order->order_status, ['cancelled', 'refunded'], true))")
needle='            $this->inventoryService->deductForOrder('
pos=s.index(needle)
s=s[:pos]+'''            if (in_array($order->payment_status, ['paid', 'refunded'], true)) {
                return $order;
            }
            if (in_array($order->order_status, ['cancelled', 'refunded'], true)) {
                throw new \\RuntimeException('A closed order requires payment reconciliation before fulfillment.');
            }
            $oldPaymentStatus = $order->payment_status;
            $oldOrderStatus = $order->order_status;

'''+s[pos:]
s=s.replace('            return $order;\n\n        });', '''            $activity = app(\\App\\Services\\OrderActivityService::class);
            $activity->paymentStatusChanged($order, $oldPaymentStatus, $order->payment_status);
            if ($oldOrderStatus !== $order->order_status) {
                $activity->orderStatusChanged($order, $oldOrderStatus, $order->order_status);
            }
            return $order;

        });''',1)
s=s.replace('        $this->orderEmailService->sendOrderEmails(\n\n            $order->fresh()\n\n        );', "        if ($order->payment_status === 'paid') {\n            // Existing notification logs deduplicate delivery and permit retry.\n            $this->orderEmailService->sendOrderEmails($order->fresh());\n        }",1)
save(p,s)

# Inventory's own locked lookup must preserve the archive visibility policy.
p='app/Services/InventoryService.php';s=read(p).replace('Order::query()', 'Order::withTrashed()');save(p,s)

# Bank payments: retain archived records, reject invalid terminal transitions
# inside the locked transaction, and record customer-safe activity.
p='app/Http/Controllers/Admin/PaymentVerificationController.php';s=compact(read(p))
s=s.replace('Order::query()', 'Order::withTrashed()')
needle='                    if ($lockedOrder->payment_status === \'paid\') {'
guard='''                    if (in_array($lockedOrder->order_status, ['cancelled', 'refunded'], true)
                        || $lockedOrder->payment_status === 'refunded'
                        || $lockedOrder->inventory_restored_at !== null) {
                        throw new RuntimeException('Cancelled or refunded orders cannot be reviewed as new bank payments.');
                    }
                    if ($lockedOrder->payment_provider !== 'bank_transfer'
                        && $lockedOrder->payment_method !== 'bank_transfer') {
                        throw new RuntimeException('Only bank-transfer orders can be reviewed manually.');
                    }
                    $oldPaymentStatus = $lockedOrder->payment_status;
                    $oldOrderStatus = $lockedOrder->order_status;

'''
assert s.count(needle)==2;s=s.replace(needle,guard+needle)
s=s.replace('                    $verifiedNow = true;', '''                    $activity = app(\\App\\Services\\OrderActivityService::class);
                    $activity->paymentStatusChanged($lockedOrder, $oldPaymentStatus, 'paid');
                    if ($oldOrderStatus !== $lockedOrder->order_status) {
                        $activity->orderStatusChanged($lockedOrder, $oldOrderStatus, $lockedOrder->order_status);
                    }
                    $verifiedNow = true;''')
s=s.replace('                    $rejectedNow = true;', '''                    if ($oldPaymentStatus !== 'failed') {
                        app(\\App\\Services\\OrderActivityService::class)
                            ->paymentStatusChanged($lockedOrder, $oldPaymentStatus, 'failed');
                    }
                    $rejectedNow = true;''')
save(p,s)

# Use both the durable redemption ledger and legacy paid order records.
p='app/Services/CouponService.php';s=read(p).replace('Order::query()', 'Order::withTrashed()')
s=s.replace('        $effectiveGlobalUses = max(', '''        $ledgerGlobalUses = \\App\\Models\\CouponRedemption::where('coupon_id', $coupon->id)->count();
        $effectiveGlobalUses = max(''')
s=s.replace('            (int) $paidGlobalUses\n', '            (int) $paidGlobalUses,\n            (int) $ledgerGlobalUses\n')
s=s.replace('            if ($userUses >= (int) $coupon->per_user_usage_limit)', '''            $userUses = max($userUses, \\App\\Models\\CouponRedemption::where('coupon_id', $coupon->id)
                ->where('user_id', $userId)->count());
            if ($userUses >= (int) $coupon->per_user_usage_limit)''')
save(p,s)

# Redemption also respects pre-ledger purchases and archived records. Exclude
# the current paid order when comparing historical usage against its limit.
p='app/Services/CouponRedemptionService.php';s=read(p)
s=s.replace("        if (\n            $coupon->usage_limit !== null", """        $usedCount = max($usedCount, (int) $coupon->used_count,
            Order::withTrashed()->whereKeyNot($order->id)->where('payment_status', 'paid')
                ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($code)])->count());

        if (
            $coupon->usage_limit !== null""",1)
s=s.replace("            if ($customerUses >= (int) $coupon->per_user_usage_limit)", """            $customerUses = max($customerUses,
                Order::withTrashed()->whereKeyNot($order->id)->where('user_id', $order->user_id)
                    ->where('payment_status', 'paid')
                    ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($code)])->count());
            if ($customerUses >= (int) $coupon->per_user_usage_limit)""")
s=s.replace("            'used_count' => CouponRedemption::query()\n                ->where('coupon_id', $coupon->id)\n                ->count(),", """            'used_count' => max($usedCount + 1, CouponRedemption::query()
                ->where('coupon_id', $coupon->id)->count()),""")
save(p,s)

# Keep coupon identifiers and history intact, including archived order refs.
p='app/Http/Controllers/Admin/CouponController.php';s=read(p)
s=s.replace('        $coupon->update($data);', '''        \\Illuminate\\Support\\Facades\\DB::transaction(function () use ($coupon, $data): void {
            $locked = Coupon::whereKey($coupon->id)->lockForUpdate()->firstOrFail();
            if ($data['code'] !== $locked->code && $this->hasOrderReferences($locked)) {
                throw \\Illuminate\\Validation\\ValidationException::withMessages([
                    'code' => 'This coupon is referenced by orders. Keep its code and create a new coupon instead.',
                ]);
            }
            $pending = \\App\\Models\\Order::withTrashed()
                ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($locked->code)])
                ->whereNotIn('payment_status', ['paid', 'refunded'])
                ->whereNotIn('order_status', ['cancelled', 'refunded'])->exists();
            foreach (['usage_limit', 'per_user_usage_limit'] as $field) {
                $old = $locked->{$field};
                $new = $data[$field];
                if ($pending && $new !== null && ($old === null || (int) $new < (int) $old)) {
                    throw \\Illuminate\\Validation\\ValidationException::withMessages([
                        $field => 'Do not reduce this limit while orders are awaiting payment.',
                    ]);
                }
            }
            $locked->update($data);
        });''')
s=s.replace("        $appliedCoupon = session('cart_coupon');", "        $appliedCoupon = session('cart_coupon');",1)
start=s.index('    public function destroy(');end=s.index('    private function validatedCoupon',start)
s=s[:start]+'''    public function destroy(Coupon $coupon): RedirectResponse
    {
        $deleted = \\Illuminate\\Support\\Facades\\DB::transaction(function () use ($coupon): bool {
            $locked = Coupon::whereKey($coupon->id)->lockForUpdate()->firstOrFail();
            if ($this->hasOrderReferences($locked)) {
                return false;
            }
            return (bool) $locked->delete();
        });
        if (!$deleted) {
            return back()->with('error', 'This coupon is referenced by orders or redemptions. Deactivate it instead; its history must be preserved.');
        }
        if ((int) session('cart_coupon.id') === (int) $coupon->id) {
            session()->forget('cart_coupon');
        }
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    private function hasOrderReferences(Coupon $coupon): bool
    {
        return \\App\\Models\\Order::withTrashed()
            ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($coupon->code)])->exists()
            || \\App\\Models\\CouponRedemption::where('coupon_id', $coupon->id)->exists()
            || (int) $coupon->used_count > 0;
    }

'''+s[end:];save(p,s)

# Reject descendant parents as well as direct self-parenting.
p='app/Http/Controllers/Admin/ProductCategoryController.php';s=read(p)
needle="                        $fail('A category cannot be its own parent.');\n                    }"
assert needle in s
s=s.replace(needle,needle+'''
                    $seen = [];
                    $cursor = (int) $value;
                    while ($cursor > 0) {
                        if (isset($seen[$cursor]) || ($productCategory && $cursor === (int) $productCategory->id)) {
                            $fail('A category cannot use a descendant or cyclic category as its parent.');
                            return;
                        }
                        $seen[$cursor] = true;
                        $cursor = (int) ProductCategory::whereKey($cursor)->value('parent_id');
                    }''');save(p,s)

# Storefront uses the same price assumptions as cart: variants inherit parent
# regular price; cart does not inherit a parent sale price. Sale zero is valid.
p='app/Http/Controllers/ShopController.php';s=read(p)
start=s.index('                    /*\n|---');end=s.index('                }\n            );',start)
s=s[:start]+s[end:]
needle='        $selectedCategories = array_filter('
s=s.replace(needle,"        if ((string) ($filters['featured'] ?? '') === '1') {\n            $query->where('is_featured', true);\n        }\n\n"+needle,1)
start=s.index("        if (\n            array_key_exists('min_price'");end=s.index('        $selectedRatings =',start)
s=s[:start]+'''        $minimum = filled($filters['min_price'] ?? null) ? (float) $filters['min_price'] : null;
        $maximum = filled($filters['max_price'] ?? null) ? (float) $filters['max_price'] : null;
        if ($minimum !== null && $maximum !== null && $minimum > $maximum) {
            throw \\Illuminate\\Validation\\ValidationException::withMessages([
                'max_price' => 'Maximum price must be at least the minimum price.',
            ]);
        }
        if ($minimum !== null || $maximum !== null) {
            $applyRange = function (Builder $priceQuery, string $sql) use ($minimum, $maximum): void {
                if ($minimum !== null) $priceQuery->whereRaw("($sql) >= CAST(? AS DECIMAL(18, 4))", [$minimum]);
                if ($maximum !== null) $priceQuery->whereRaw("($sql) <= CAST(? AS DECIMAL(18, 4))", [$maximum]);
            };
            $query->where(function (Builder $priceQuery) use ($applyRange): void {
                $priceQuery->where(function (Builder $simple) use ($applyRange): void {
                    $simple->whereDoesntHave('variants');
                    $applyRange($simple, $this->productEffectivePriceSql());
                })->orWhereHas('variants', function (Builder $variant) use ($applyRange): void {
                    $applyRange($variant, $this->variantEffectivePriceSql());
                });
            });
        }

'''+s[end:]
start=s.index('        if ($hasInStock && !$hasOutOfStock)');end=s.index('        $selectedOffers =',start)
s=s[:start]+'''        if ($hasInStock && !$hasOutOfStock) {
            $query->where(function (Builder $stock): void {
                $stock->where(function (Builder $simple): void {
                    $simple->whereDoesntHave('variants')->where('stock', '>', 0);
                })->orWhereHas('variants', fn (Builder $variant) => $variant->where('stock', '>', 0));
            });
        }
        if ($hasOutOfStock && !$hasInStock) {
            $query->where(function (Builder $stock): void {
                $stock->where(function (Builder $simple): void {
                    $simple->whereDoesntHave('variants')->where(fn (Builder $q) => $q->whereNull('stock')->orWhere('stock', '<=', 0));
                })->orWhere(function (Builder $variable): void {
                    $variable->whereHas('variants')->whereDoesntHave('variants', fn (Builder $v) => $v->where('stock', '>', 0));
                });
            });
        }

'''+s[end:]
start=s.index('        if (\n            in_array(\n                \'on_sale\'');end=s.index('        /*',start+20)
s=s[:start]+'''        if (in_array('on_sale', $selectedOffers, true)) {
            $query->where(function (Builder $discount): void {
                $discount->where(function (Builder $simple): void {
                    $simple->whereDoesntHave('variants')->whereRaw(
                        $this->productEffectivePriceSql() . ' < products.regular_price'
                    );
                })->orWhereHas('variants', function (Builder $variant): void {
                    $variant->whereRaw($this->variantEffectivePriceSql() . ' < ' . $this->variantRegularPriceSql());
                });
            });
        }

'''+s[end:]
s=s.replace('AND sale_price > 0','AND sale_price >= 0')
start=s.index('    private function variantEffectivePriceSql()');s=s[:start]+'''    private function variantRegularPriceSql(): string
    {
        return 'COALESCE(product_variants.regular_price, (SELECT p.regular_price FROM products p WHERE p.id = product_variants.product_id))';
    }

    private function variantEffectivePriceSql(): string
    {
        $regular = $this->variantRegularPriceSql();
        $sale = 'product_variants.sale_price';
        return "CASE WHEN ($sale) IS NOT NULL AND ($sale) >= 0 AND ($sale) < ($regular) THEN ($sale) ELSE ($regular) END";
    }
}
'''
s=s.replace("        $productPrices = Product::query()", "        $productPrices = Product::query()->whereDoesntHave('variants')")
s=s.replace("$this->productEffectivePriceSql() . ' ASC'", "$this->listingEffectivePriceSql() . ' ASC'")
s=s.replace("$this->productEffectivePriceSql() . ' DESC'", "$this->listingEffectivePriceSql() . ' DESC'")
needle='    private function variantRegularPriceSql(): string'
s=s.replace(needle,'''    private function listingEffectivePriceSql(): string
    {
        return 'COALESCE((SELECT MIN(' . $this->variantEffectivePriceSql()
            . ') FROM product_variants WHERE product_variants.product_id = products.id), ('
            . $this->productEffectivePriceSql() . '))';
    }

'''+needle)
save(p,s)

# Configured shipping method, persisted alongside amount on Stripe orders.
p='app/Http/Controllers/Payment/StripePaymentController.php';s=compact(read(p))
s=sub(s,r'\$this->calculateShipping\(\s*\$subtotal,\s*\$deliveryCountry\s*\)', "$this->calculateShipping($subtotal, $deliveryCountry, $validated['shipping_method'])")
s=s.replace("        return $request->validate([", "        return $request->validate([\n            'shipping_method' => ['required', \\Illuminate\\Validation\\Rule::in(array_keys((array) config('shipping.methods', [])))],",1)
s=sub(s,r"('shipping'\s*=>\s*\$shipping,)",r"\1\n                        'shipping_method' => (string) config('shipping.methods.' . $validated['shipping_method'] . '.name', ucfirst($validated['shipping_method'])),\n                        'shipping_price' => $shipping,\n                        'estimated_delivery' => (string) config('shipping.methods.' . $validated['shipping_method'] . '.delivery_time', ''),")
start=s.index('    private function calculateShipping(');end=s.index('    private function calculateTotal(',start)
s=s[:start]+'''    private function calculateShipping(float $subtotal, ?string $countryCode = null, ?string $method = null): float
    {
        $method ??= (string) config('shipping.default', 'standard');
        $configured = config('shipping.methods.' . $method);
        if (!is_array($configured)) {
            abort(422, 'The selected shipping method is unavailable.');
        }
        return round(max(0, (float) ($configured['price'] ?? 0)), 2);
    }

'''+s[end:]
needle="        $fields = [";pos=s.index(needle,s.index('    private function pendingOrderMatchesCheckout('))
s=s[:pos]+s[pos:].replace(needle,"        $fields = [\n            'shipping_method' => (string) config('shipping.methods.' . $validated['shipping_method'] . '.name', ucfirst($validated['shipping_method'])),",1)
save(p,s)

# Routes: avoid broken unused details and allow durable bank payment binding.
p='routes/web.php';s=read(p)
for controller in ['AdminProductCategoryController','AdminProductTagController','AdminCouponController']:
    s=sub(s,rf'({controller}::class\s*)\);',r"\1)->except(['show']);")
for name in ['show','verify-bank-transfer','reject-bank-transfer']:
    old=f")->name('payment-verifications.{name}');";assert old in s
    s=s.replace(old,f")->withTrashed()->name('payment-verifications.{name}');")
for name in ['show','invoice','invoice.download','packing-slip','packing-slip.download','shipping-label','shipping-label.download']:
    old=f")->name('orders.{name}');";assert old in s
    s=s.replace(old,f")->withTrashed()->name('orders.{name}');")
save(p,s)

# Existing role permissions apply to every endpoint in the seven modules.
p='app/Http/Middleware/AdminMiddleware.php';s=read(p)
needle='        if (\n            filled($permission)'
s=s.replace(needle,'''        if (blank($permission)) {
            $permission = match (true) {
                $request->routeIs('admin.orders.*', 'admin.payment-verifications.*') => 'orders.manage',
                $request->routeIs('admin.products.*', 'admin.product-categories.*', 'admin.product-tags.*') => 'products.manage',
                $request->routeIs('admin.coupons.*') => 'coupons.manage',
                default => null,
            };
        }

'''+needle);save(p,s)

# Sidebar: align visibility with endpoint permission; archived active state.
p='resources/views/admin/partials/sidebar.blade.php';s=read(p)
s=s.replace("request()->routeIs('admin.orders.*')", "request()->routeIs('admin.orders.*') && !request()->routeIs('admin.orders.archived', 'admin.orders.restore', 'admin.orders.bulk-restore')")
for route,perm in [('orders.index','orders.manage'),('orders.archived','orders.manage'),('payment-verifications.index','orders.manage'),('products.index','products.manage'),('product-categories.index','products.manage'),('product-tags.index','products.manage'),('coupons.index','coupons.manage')]:
    pattern=rf"(<a\s+href=\"\{{\{{ route\('admin\.{re.escape(route)}'\) \}}\}}\".*?</a>)"
    s=sub(s,pattern,rf"@if(auth('admin')->user()?->hasAdminPermission('{perm}'))\n\1\n@endif")
save(p,s)

# Badge composers belong to provider, and only actual sidebar view targets.
p='app/Providers/AppServiceProvider.php';s=read(p)
s=s.replace("View::composer('admin.layouts.sidebar'", "View::composer(['admin.partials.sidebar', 'admin.layouts.sidebar']")
s=s.replace("            $view->with('reorderRequiredCount', $reorderRequiredCount);", "            $view->with('reorderRequiredCount', $reorderRequiredCount);\n            $view->with('activeInventoryAlertCount', Schema::hasTable('inventory_alerts')\n                ? \\App\\Models\\InventoryAlert::where('status', 'active')->count() : 0);")
save(p,s)

# UI matches the server's terminal-state protections.
p='resources/views/admin/payment-verifications/show.blade.php';s=read(p)
s=s.replace('@if($isBank && !$verified && !$rejected)', "@if($isBank && !$verified && !$rejected && !in_array($order->order_status, ['cancelled', 'refunded'], true) && $order->payment_status !== 'refunded' && $order->inventory_restored_at === null)")
save(p,s)

# Preserve already-passing admin product/order/tag behavior as full files so
# the complete seven-menu source handover includes every module controller.
for p in ['app/Http/Controllers/Admin/OrderController.php','app/Http/Controllers/Admin/ProductController.php','app/Http/Controllers/Admin/ProductTagController.php']:
    save(p,read(p))

# Public tracking keeps its existing order-number/email ownership checks.
p='app/Http/Controllers/OrderTrackingController.php';s=read(p).replace('Order::query()', 'Order::withTrashed()');save(p,s)

(OUT/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
print(f'Prepared {len(manifest)} complete replacement TXT files. Application sources untouched.')
