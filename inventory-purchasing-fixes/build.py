from pathlib import Path
import re,json,hashlib
R=Path(r'C:\xampp\htdocs\ArizonaOutfits'); O=R/'inventory-purchasing-fixes'; S=O/'_staged'; files={}
def read(p):return files.get(p,(R/p).read_text(encoding='utf-8-sig') if (R/p).exists() else '')
def put(p,s):files[p]=s
def sub(p,a,b):
 s=read(p);assert a in s,(p,a);put(p,s.replace(a,b))
def method(p,n,body):
 s=read(p);m=re.search(r'^    (?:public|private|protected)(?: static)? function '+n+r'\(',s,re.M);assert m,(p,n)
 opening=s.index('{',m.start());depth=1;i=opening+1
 # PHP method bodies here contain balanced braces in quoted strings/comments.
 while depth:
  depth+= (s[i]=='{')-(s[i]=='}');i+=1
 put(p,s[:m.start()]+body+'\n'+s[i:])

# Keep customer ownership columns intact; new administrator IDs have separate FKs.
actors={'Supplier':{'creator':'created_by'},'PurchaseOrderReceipt':{'receiver':'received_by'},'SupplierReturn':{'creator':'created_by','completer':'completed_by','canceller':'cancelled_by'},'SupplierDocument':{'uploader':'uploaded_by'},'SupplierRating':{'ratedBy':'rated_by'},'SupplierPurchaseOrderDelivery':{'sentBy':'sent_by'}}
for model,rels in actors.items():
 p=f'app/Models/{model}.php'
 for relation,column in rels.items():
  sub(p,f"'{column}',",f"'{column}',\n        '{column}_admin_id',")
  # Preserve eager-loadable legacy relation and expose a resolved actor accessor.
  method(p,relation,f'''    public function {relation}(): BelongsTo
    {{
        return $this->belongsTo(User::class, '{column}');
    }}

    public function {relation}Admin(): BelongsTo
    {{
        return $this->belongsTo(Admin::class, '{column}_admin_id');
    }}

    public function get{relation[0].upper()+relation[1:]}ActorAttribute(): Admin|User|null
    {{
        return $this->{relation}Admin ?? $this->{relation};
    }}''')
controllers={'SupplierController': ['created_by'],'SupplierDocumentController':['uploaded_by'],'SupplierRatingController':['rated_by'],'SupplierPurchaseOrderDeliveryController':['sent_by'],'PurchaseOrderReceivingController':['received_by','created_by','completed_by','cancelled_by']}
for c,cols in controllers.items():
 p=f'app/Http/Controllers/Admin/{c}.php'
 for col in cols:
  s=read(p)
  s=re.sub(r"('"+col+r"'\s*=>\s*)Auth::id\(\)",f"'{col}' => null, '{col}_admin_id' => Auth::guard('admin')->id()",s)
  s=re.sub(r"\$validated\['"+col+r"'\]\s*=\s*Auth::id\(\);",f"$validated['{col}'] = null;\n        $validated['{col}_admin_id'] = Auth::guard('admin')->id();",s)
  put(p,s)
for p in ['resources/views/admin/suppliers/show.blade.php','resources/views/admin/purchase-orders/receiving.blade.php']:
 s=read(p)
 for model,rels in actors.items():
  var={'Supplier':'supplier','SupplierDocument':'document','SupplierRating':'rating','SupplierReturn':'supplierReturn','SupplierPurchaseOrderDelivery':'delivery','PurchaseOrderReceipt':'receipt'}[model]
  for rel in rels:s=re.sub(r'(\$'+var+r'\s*->\s*)'+rel+r'\b',r'\1'+rel+'_actor',s)
 put(p,s)

p='app/Services/InventoryHistoryService.php'
sub(p,'use App\\Models\\User;','use App\\Models\\User;\nuse App\\Models\\Admin;\nuse Illuminate\\Contracts\\Auth\\Authenticatable;')
s=read(p).replace('?User $user','?Authenticatable $user').replace('User $user','Authenticatable $user').replace("'user_id' => $user?->id,","'user_id' => $user instanceof User ? $user->getAuthIdentifier() : null,\n            'admin_id' => $user instanceof Admin ? $user->getAuthIdentifier() : null,");put(p,s)
p='app/Services/InventoryAdjustmentService.php'
sub(p,'use App\\Models\\User;','use Illuminate\\Contracts\\Auth\\Authenticatable;')
sub(p,'User $user','Authenticatable $user')
s=read(p);s=s.replace('            $movement = match ($type)', '            $movement = match ($type)')
for entity in ['product','variant']:
 s=s.replace(f'            ${entity}->update([',f'''            if ($quantity < 0 || $after < 0) {{
                throw new RuntimeException('Stock cannot be negative. Reduce the removal quantity.');
            }}
            ${entity}->update([''')
s=s.replace('            $before = (int) $product->stock;',"            if ($product->variants()->exists()) {\n                throw new RuntimeException('Adjust the individual variant stock for this product.');\n            }\n            $before = (int) $product->stock;")
put(p,s)
p='app/Models/InventoryHistory.php';method(p,'order', '''    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }''')
p='app/Http/Controllers/Admin/InventoryHistoryController.php'
sub(p,"                'user',","                'user',\n                'admin',")
sub(p,"->orWhereHas('user', function", "->orWhereHas('admin', function ($query) use ($search) {\n                        $query->where('name', 'like', \"%{$search}%\")->orWhere('email', 'like', \"%{$search}%\");\n                    })\n                    ->orWhereHas('variant', fn ($query) => $query->where('sku', 'like', \"%{$search}%\"))\n                    ->orWhereHas('user', function")
p='resources/views/admin/inventory-history/index.blade.php';s=read(p);s=re.sub(r'\$item->user\s*\?',r'$item->admin ?',s);put(p,s)

p='app/Http/Middleware/AdminMiddleware.php';sub(p,"$request->routeIs('admin.orders.*'", "$request->routeIs('admin.inventory-alerts.*', 'admin.inventory-history.*', 'admin.inventory-reports.*', 'admin.stock-valuation.*', 'admin.reorder-dashboard.*', 'admin.products.inventory.*') => 'inventory.manage',\n                $request->routeIs('admin.purchase-orders.*') => 'purchase-orders.manage',\n                $request->routeIs('admin.suppliers.*') => 'suppliers.manage',\n                $request->routeIs('admin.orders.*'")
p='resources/views/admin/partials/sidebar.blade.php';s=read(p)
for routes,permission in [(['inventory-alerts','inventory-history','inventory-reports','stock-valuation','reorder-dashboard'],'inventory.manage'),(['purchase-orders'],'purchase-orders.manage'),(['suppliers'],'suppliers.manage')]:
 for route in routes:
  match=re.search(r'<a\b[^>]*?href="\{\{\s*route\(\s*\'admin\.'+route+r'\.index\'[\s\S]*?</a>',s)
  assert match,route
  s=s[:match.start()]+f"@if(auth('admin')->user()?->hasAdminPermission('{permission}'))\n"+match[0]+"\n@endif"+s[match.end():]
put(p,s)

p='app/Models/PurchaseOrder.php';sub(p,"        'admin_id',","        'admin_id',\n        'lock_version',")
sub(p,"        'order_date' => 'date',","        'lock_version' => 'integer',\n        'order_date' => 'date',")
sub(p,'        static::creating(','''        static::updating(function (PurchaseOrder $purchaseOrder): void {
            $purchaseOrder->lock_version = (int) $purchaseOrder->getOriginal('lock_version') + 1;
        });
        static::creating(''')
sub(p,"            if (blank($purchaseOrder->reference))", "            $purchaseOrder->lock_version ??= 0;\n            if (blank($purchaseOrder->reference))")
p='app/Http/Controllers/Admin/PurchaseOrderDraftController.php';s=read(p);s=re.sub(r'\$currentVersion = \(int\) optional\(\s*\$lockedOrder->updated_at\s*\)->getTimestamp\(\);', '$currentVersion = (int) $lockedOrder->lock_version;',s);put(p,s)
p='resources/views/admin/purchase-orders/edit.blade.php';sub(p,"optional($purchaseOrder->updated_at)->getTimestamp() ?? 0","$purchaseOrder->lock_version ?? 0")
p='app/Http/Controllers/Admin/PurchaseOrderController.php';s=read(p);s=re.sub(r"('items\.\*\.identifier'\s*=>\s*\[)",r"\1'distinct', ",s);put(p,s)
method(p,'markOrdered', '''    public function markOrdered(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        [$kind, $message] = DB::transaction(function () use ($purchaseOrder): array {
            $locked = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);
            if ($locked->isOrdered()) return ['success', 'This purchase order is already marked as ordered.'];
            if (!$locked->isDraft() || $locked->items()->sum('quantity_received') > 0) {
                return ['error', 'Only an unreceived draft can be marked as ordered.'];
            }
            if (!$locked->items()->exists()) return ['error', 'This purchase order has no items.'];
            $locked->forceFill(['status' => PurchaseOrder::STATUS_ORDERED, 'ordered_at' => now(), 'cancelled_at' => null])->save();
            return ['success', 'Purchase order was marked as ordered.'];
        });
        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with($kind, $message);
    }''')
method(p,'cancel', '''    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        [$kind, $message] = DB::transaction(function () use ($purchaseOrder): array {
            $locked = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);
            if ($locked->isCancelled()) return ['success', 'This purchase order is already cancelled.'];
            if (!in_array($locked->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_ORDERED], true)
                || $locked->items()->sum('quantity_received') > 0) {
                return ['error', 'This purchase order cannot be cancelled because inventory has been received.'];
            }
            $locked->forceFill(['status' => PurchaseOrder::STATUS_CANCELLED, 'cancelled_at' => now(), 'received_at' => null])->save();
            return ['success', 'Purchase order was cancelled successfully.'];
        });
        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with($kind, $message);
    }''')
p='app/Http/Controllers/Admin/PurchaseOrderReceivingController.php';sub(p,"'user_id' => Auth::id(),","'user_id' => null,\n            'admin_id' => Auth::guard('admin')->id(),")
sub(p,"        return [$stockBefore, $stockAfter];",'''        DB::afterCommit(function () use ($stockModel): void {
            $alerts = app(\\App\\Services\\InventoryAlertService::class);
            if ($stockModel instanceof ProductVariant) $alerts->checkVariant($stockModel);
            else $alerts->checkProduct($stockModel);
        });
        return [$stockBefore, $stockAfter];''')

# Shared read-only Product projections keep current Blade pages/export interfaces.
put('app/Services/InventoryCatalogService.php', '''<?php
namespace App\\Services;

use App\\Models\\Product;
use App\\Models\\ProductVariant;
use Illuminate\\Support\\Collection;

class InventoryCatalogService
{
    public static function threshold(Product|ProductVariant $item): int
    {
        return max(0, (int) ($item->reorder_point ?? config('inventory.low_stock_threshold', 5)));
    }

    /** Read-only projections: never save these copies of catalog products. */
    public function rows(): Collection
    {
        return Product::with(['categories', 'variants'])->orderBy('title')->get()->flatMap(function (Product $product) {
            $entities = $product->variants->isEmpty() ? collect([$product]) : $product->variants;
            return $entities->map(function ($entity) use ($product) {
                $row = clone $product;
                $variant = $entity instanceof ProductVariant;
                $label = $variant ? collect($entity->options ?? [])->map(fn ($value, $key) => is_scalar($value) ? $key . ': ' . $value : json_encode($value))->implode(', ') : '';
                $row->title = $product->title . ($label !== '' ? ' — ' . $label : '');
                $row->sku = $entity->sku ?: $product->sku;
                $row->stock = max(0, (int) $entity->stock);
                $row->inventory_variant_id = $variant ? $entity->id : null;
                $row->reorder_point = self::threshold($entity);
                $row->regular_price = $entity->regular_price !== null ? $entity->regular_price : $product->regular_price;
                $row->sale_price = $entity->sale_price;
                $row->cost_price = max(0, (float) $product->cost_price);
                $regular = max(0, (float) $row->regular_price);
                $sale = $row->sale_price !== null ? (float) $row->sale_price : null;
                $row->selling_price = $sale !== null && $sale >= 0 && $sale < $regular ? $sale : $regular;
                $row->inventory_cost = round($row->stock * $row->cost_price, 2);
                $row->inventory_retail = round($row->stock * $row->selling_price, 2);
                $row->inventory_profit = round($row->inventory_retail - $row->inventory_cost, 2);
                $row->margin = $row->selling_price > 0 ? round(($row->selling_price - $row->cost_price) / $row->selling_price * 100, 2) : ($row->cost_price > 0 ? -100 : 0);
                return $row;
            });
        })->values();
    }
}
''')
p='app/Services/InventoryAlertService.php'
s=read(p);s=re.sub(r'\$threshold = max\(\s*0,\s*\(int\) config\(\s*\'inventory.low_stock_threshold\',\s*5\s*\)\s*\);', '$threshold = InventoryCatalogService::threshold($variant ?? $product);',s)
s=s.replace('            $product->refresh();', '''            $product->refresh();
            if ($product->variants()->exists()) {
                $this->resolveActiveAlerts($product, null);
                foreach ($product->variants as $variant) $this->checkVariant($variant);
                return;
            }''');put(p,s)
p='app/Http/Controllers/Admin/ReorderDashboardController.php';s=read(p);s=re.sub(r'max\(\s*0,\s*\$reorderPoint - \$stock\s*\)', 'max(1, $reorderPoint - $stock)',s).replace('$reorderPointValue ?? 5',"$reorderPointValue ?? config('inventory.low_stock_threshold', 5)");put(p,s)
p='app/Http/Controllers/Admin/PurchaseOrderController.php';s=read(p).replace('$product->reorder_point ?? 5',"$product->reorder_point ?? config('inventory.low_stock_threshold', 5)").replace('$variant->reorder_point ?? 5',"$variant->reorder_point ?? config('inventory.low_stock_threshold', 5)");put(p,s)
p='app/Http/Controllers/Admin/StockValuationController.php'
method(p,'getValuationProducts', '''    private function getValuationProducts()
    {
        return app(\\App\\Services\\InventoryCatalogService::class)->rows();
    }''')
s=read(p);s=re.sub(r'\$stock <=\s*5', '$stock <= (int) $product->reorder_point',s);put(p,s)
p='app/Exports/StockValuationExport.php'
method(p,'collection','''    public function collection(): Collection
    {
        return $this->prepareProducts($this->products ?? app(\\App\\Services\\InventoryCatalogService::class)->rows());
    }''')
s=read(p).replace('$salePrice > 0','$salePrice >= 0').replace('$stock <= 5',"$stock <= \\App\\Services\\InventoryCatalogService::threshold($product)")
s=s.replace('                : 0;','                : ($costPrice > 0 ? -100 : 0);');put(p,s)
p='app/Http/Controllers/Admin/InventoryReportController.php';s=read(p)
a=s.index('        $totalProductStock =');b=s.index('        /*',a)
s=s[:a]+'''        $inventory = app(\\App\\Services\\InventoryCatalogService::class)->rows();
        $totalProductStock = (int) $inventory->whereNull('inventory_variant_id')->sum('stock');
        $totalVariantStock = (int) $inventory->whereNotNull('inventory_variant_id')->sum('stock');
        $totalStockUnits = $totalProductStock + $totalVariantStock;
        $outOfStockProducts = $inventory->filter(fn ($item) => $item->stock <= 0)->count();
        $lowStockProducts = $inventory->filter(fn ($item) => $item->stock > 0 && $item->stock <= $item->reorder_point)->count();

'''+s[b:]
s=s.replace('$recentMovements = InventoryHistory::query()', '$recentMovements = (clone $movementQuery)').replace("                'user',","                'user',\n                'admin',")
a=s.index('        $lowStockItems =');b=s.index('        return view(',a)
s=s[:a]+'''        $lowStockItems = $inventory->filter(fn ($item) => $item->stock > 0 && $item->stock <= $item->reorder_point)->sortBy('stock')->take(15)->values();
        $outOfStockItems = $inventory->filter(fn ($item) => $item->stock <= 0)->sortByDesc('updated_at')->take(15)->values();

'''+s[b:]
s=s.replace('        $endDate = now()->endOfDay();', '''        $request->validate([
            'date_range' => ['nullable', 'in:today,7_days,30_days,90_days,this_month,last_month,custom'],
            'start_date' => ['required_if:date_range,custom', 'nullable', 'date'],
            'end_date' => ['required_if:date_range,custom', 'nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $endDate = now()->endOfDay();''')
s=re.sub(r'\$row->variant\?->name\s*\?\? \$row->variant\?->title',"$row->variant ? collect($row->variant->options ?? [])->map(fn ($value, $key) => is_scalar($value) ? $key . ': ' . $value : json_encode($value))->implode(', ') : null",s)
s=s.replace('$row->product?->sku,','$row->variant?->sku ?: $row->product?->sku,').replace('$row->user?->name,','$row->performed_by,');put(p,s)
for p in ['resources/views/admin/stock-valuation/index.blade.php','resources/views/admin/stock-valuation/pdf.blade.php']:
 s=read(p);s=re.sub(r'\$stock\s*<=\s*5', '$stock <= (int) $product->reorder_point',s).replace('(int) $product->stock <= 5','(int) $product->stock <= (int) $product->reorder_point');put(p,s)
p='resources/views/admin/stock-valuation/index.blade.php';sub(p,'data-stock-value="{{ $stock }}"','data-stock-value="{{ $stock }}" data-reorder-point="{{ $product->reorder_point }}"');sub(p,'stockQuantity <= 5','stockQuantity <= numberValue(row.dataset.reorderPoint)')
p='app/Http/Controllers/Admin/PurchaseOrderReceivingController.php';s=read(p)
for rel in ['receiver','creator','completer','canceller']:s=s.replace(f"'{rel}:id,name,email',",f"'{rel}:id,name,email', '{rel}Admin:id,name,email',")
put(p,s)

# The remaining currency and migration repairs are in the next build segment.
put('app/Services/PurchasingCurrencyService.php', '''<?php
namespace App\\Services;
use Illuminate\\Support\\Collection;
class PurchasingCurrencyService
{
    public static function totals(Collection $orders): array
    {
        return $orders->where('status', '!=', 'cancelled')->groupBy(fn ($order) => strtoupper($order->currency ?: 'GBP'))
            ->map(fn ($group) => round((float) $group->sum('total_amount'), 2))->sortKeys()->all();
    }
    public static function format(array $totals): string
    {
        return collect($totals)->map(fn ($total, $currency) => $currency . ' ' . number_format($total, 2))->implode(' · ') ?: 'No purchases';
    }
}
''')
p='config/inventory.php';sub(p,'return [',"return [\n    // Existing catalog costs/prices were displayed in GBP. Set this explicitly if your catalog uses another currency.\n    'currency' => strtoupper(env('INVENTORY_CURRENCY', 'GBP')),\n")
p='app/Http/Controllers/Admin/PurchaseOrderController.php';s=read(p);a=s.index('        $openOrderValue =');b=s.index('        return view(',a)
s=s[:a]+'''        $allOrders = PurchaseOrder::query()->get(['status', 'currency', 'total_amount']);
        $openOrderValue = \\App\\Services\\PurchasingCurrencyService::totals($allOrders->whereIn('status', ['draft', 'ordered', 'partially_received']));
        $totalPurchaseValue = \\App\\Services\\PurchasingCurrencyService::totals($allOrders);

'''+s[b:];put(p,s)
p='app/Http/Controllers/Admin/SupplierController.php';s=read(p);a=s.index('        $totalSupplierSpend =');b=s.index('        return view(',a)
s=s[:a]+'''        $totalSupplierSpend = \\App\\Services\\PurchasingCurrencyService::totals(
            \\App\\Models\\PurchaseOrder::whereHas('supplier')->get(['status', 'currency', 'total_amount'])
        );
        foreach ($suppliers as $listedSupplier) {
            $listedSupplier->spend_by_currency = \\App\\Services\\PurchasingCurrencyService::totals($listedSupplier->purchaseOrders);
        }

'''+s[b:]
# A supplier's existing order currencies can differ after its currency is edited.
s=s.replace("        $nonCancelledPurchaseOrders =", "        $supplierCurrency = strtoupper($supplier->currency ?: 'GBP');\n        $spendByCurrency = \\App\\Services\\PurchasingCurrencyService::totals($supplier->purchaseOrders);\n        $averageByCurrency = $supplier->purchaseOrders->where('status', '!=', 'cancelled')->groupBy('currency')->map(fn ($orders) => round((float) $orders->avg('total_amount'), 2))->all();\n        $nonCancelledPurchaseOrders =",1)
s=s.replace("        $cancelledPurchaseOrders =", "        // The monthly chart uses the supplier's current currency only; summary cards show every currency.\n        $nonCancelledPurchaseOrders = $nonCancelledPurchaseOrders->where('currency', $supplierCurrency);\n        $cancelledPurchaseOrders =")
s=s.replace("                'supplierAnalytics'", "                'supplierCurrency', 'spendByCurrency', 'averageByCurrency',\n                'supplierAnalytics'")
# Eager-load both actor tables; no ID guessing and no per-row extra queries.
s=s.replace("'documents.uploader',","'documents.uploader', 'documents.uploaderAdmin',").replace("'ratings.ratedBy',","'ratings.ratedBy', 'ratings.ratedByAdmin',").replace("'purchaseOrderDeliveries.sentBy',","'purchaseOrderDeliveries.sentBy', 'purchaseOrderDeliveries.sentByAdmin',").replace("            'creator',","            'creator', 'creatorAdmin',")
put(p,s)
for p,vars in [('resources/views/admin/purchase-orders/index.blade.php',['openOrderValue','totalPurchaseValue']),('resources/views/admin/suppliers/index.blade.php',['totalSupplierSpend'])]:
 s=read(p)
 for v in vars:s=re.sub(r'£\{\{\s*number_format\(\$'+v+r',\s*2\)\s*\}\}',r'{{ \\App\\Services\\PurchasingCurrencyService::format($'+v+') }}',s)
 if 'suppliers/' in p:s=re.sub(r'£\{\{\s*number_format\(\s*\$supplier->total_spend \?\? 0,\s*2\s*\)\s*\}\}',r'{{ \\App\\Services\\PurchasingCurrencyService::format($supplier->spend_by_currency ?? []) }}',s)
 else:s=s.replace('£{{', '{{ $purchaseOrder->currency }} {{')
 put(p,s)
p='resources/views/admin/suppliers/show.blade.php';s=read(p)
for v,target in [('totalSpend','spendByCurrency'),('averageOrderValue','averageByCurrency')]:s=re.sub(r'£\{\{\s*number_format\(\$'+v+r',\s*2\)\s*\}\}',r'{{ \\App\\Services\\PurchasingCurrencyService::format($'+target+') }}',s)
s=s.replace('£{{','{{ $supplierCurrency }} {{');s=re.sub(r'\{\{ \$supplierCurrency \}\}(\s*\{\{\s*number_format\(\s*\$purchaseOrder)',r'{{ $purchaseOrder->currency }}\1',s)
put(p,s)
for p in ['resources/views/admin/purchase-orders/show.blade.php','resources/views/admin/purchase-orders/pdf.blade.php']:
 s=read(p).replace('£','{{ $purchaseOrder->currency }} ');put(p,s)
p='resources/views/admin/purchase-orders/create.blade.php';s=read(p).replace('£', "{{ old('currency', 'GBP') }} ")
# Currency comes from the selected supplier; data-currency already exists in the form.
s=s.replace("currency: 'GBP'", "currency: document.querySelector('#supplier_id')?.selectedOptions[0]?.dataset.currency || 'GBP'")
s=s.replace("{{ old('currency', 'GBP') }} ", '<span data-currency-label>{{ old(\'currency\', \'GBP\') }}</span> ')
s=s.replace('            function updateTotals() {', '''            function updateTotals() {
                const currency = document.querySelector('#supplier_id')?.selectedOptions[0]?.dataset.currency || 'GBP';
                document.querySelectorAll('[data-currency-label]').forEach(element => element.textContent = currency);''')
s=re.sub(r"('change',\s*)updateSupplierPreview",r'\1() => { updateSupplierPreview(); updateTotals(); }',s)
# Use actual selector ID discovered from the existing form below if different.
supplierselect=re.search(r'<select[^>]*id="([^"]+)"[^>]*name="supplier_id"',s)
if supplierselect:s=s.replace("'#supplier_id'", "'#"+supplierselect[1]+"'")
put(p,s)
for p in ['app/Http/Controllers/Admin/SupplierController.php','app/Http/Controllers/Admin/PurchaseOrderController.php']:
 s=read(p)
 s=re.sub(r"('highest-spend'\s*=>\s*\$query)\s*->orderByDesc",r"\1->orderBy('currency')->orderByDesc",s)
 for name in ['highest-total','lowest-total']:
  s=re.sub(r"('"+name+r"'\s*=>\s*\$query)\s*->orderBy",r"\1->orderBy('currency')->orderBy",s)
 put(p,s)
for p in ['resources/views/admin/suppliers/index.blade.php','resources/views/admin/purchase-orders/index.blade.php']:
 s=read(p).replace('Highest Spend','Highest Spend Within Currency').replace('Highest Total','Highest Total Within Currency').replace('Lowest Total','Lowest Total Within Currency');put(p,s)
for p in list((R/'resources/views/admin/stock-valuation').rglob('*.blade.php'))+list((R/'resources/views/admin/reorder-dashboard').rglob('*.blade.php')):
 p=str(p.relative_to(R)).replace('\\','/');s=read(p)
 if '£' in s or "currency: 'GBP'" in s:
  s=s.replace("'£'", "(@json(config('inventory.currency', 'GBP')) + ' ')")
  s=s.replace('£',"{{ config('inventory.currency', 'GBP') }} ").replace("currency: 'GBP'", "currency: @json(config('inventory.currency', 'GBP'))")
  put(p,s)
for p in ['app/Exports/PurchaseOrderExport.php','app/Exports/StockValuationExport.php','app/Exports/ReorderDashboardExport.php']:
 s=read(p);expr="$this->purchaseOrder->currency" if 'PurchaseOrderExport' in p else "config('inventory.currency', 'GBP')"
 # Currency code is quoted as literal text in Excel formats.
 s=re.sub(r"'([^'\n]*£[^'\n]*)'",lambda m:"str_replace('£', '\"' . "+expr+" . '\" ', '"+m[1]+"')",s);put(p,s)

migration='database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php'
ownership={model: list(rels.values()) for model,rels in actors.items()}
tables={'Supplier':'suppliers','PurchaseOrderReceipt':'purchase_order_receipts','SupplierReturn':'supplier_returns','SupplierDocument':'supplier_documents','SupplierRating':'supplier_ratings','SupplierPurchaseOrderDelivery':'supplier_purchase_order_deliveries'}
mapping=',\n            '.join("'"+tables[m]+"' => ["+', '.join("'"+c+"_admin_id'" for c in cols)+']' for m,cols in ownership.items())
put(migration,'''<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Historical placeholder migrations did not create these tables on fresh installations.
        if (!Schema::hasTable('supplier_documents')) {
            Schema::create('supplier_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->string('document_type')->nullable(); $table->string('title');
                $table->string('file_name'); $table->string('file_path');
                $table->string('mime_type')->nullable(); $table->unsignedBigInteger('file_size')->nullable();
                $table->date('expiry_date')->nullable(); $table->text('notes')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('supplier_ratings')) {
            Schema::create('supplier_ratings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                foreach (['quality_rating', 'pricing_rating', 'delivery_rating', 'communication_rating', 'reliability_rating'] as $column) $table->unsignedTinyInteger($column)->nullable();
                $table->decimal('overall_rating', 3, 2)->nullable(); $table->text('review')->nullable();
                $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        foreach (['title', 'would_recommend', 'rated_at'] as $column) {
            if (!Schema::hasColumn('supplier_ratings', $column)) {
                Schema::table('supplier_ratings', function (Blueprint $table) use ($column): void {
                    match ($column) {
                        'title' => $table->string('title')->nullable(),
                        'would_recommend' => $table->boolean('would_recommend')->nullable(),
                        'rated_at' => $table->timestamp('rated_at')->nullable(),
                    };
                });
            }
        }
        foreach ([
            '''+mapping+'''
        ] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) continue;
            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    Schema::table($tableName, function (Blueprint $table) use ($column): void {
                        $table->foreignId($column)->nullable()->constrained('admins')->nullOnDelete();
                    });
                }
            }
        }
        if (!Schema::hasColumn('purchase_orders', 'lock_version')) {
            Schema::table('purchase_orders', fn (Blueprint $table) => $table->unsignedBigInteger('lock_version')->default(0));
        }
        // Existing user IDs remain unchanged. Equal numeric admin IDs are never inferred.
    }
    public function down(): void
    {
        // Intentionally preserve additive schema and historical attribution on rollback.
        // Restore a database backup if a complete pre-installation rollback is needed.
    }
};
''')

manifest=[]
for n,(path,content) in enumerate(files.items(),1):
 target=S/path;target.parent.mkdir(parents=True,exist_ok=True);target.write_text(content,encoding='utf-8')
 name=f'{n:02d}_{Path(path).name}.txt';(O/name).write_text(content,encoding='utf-8')
 manifest.append({'txt':name,'destination':str(R/path),'relative':path,'new':not (R/path).exists(),'original_sha256':hashlib.sha256((R/path).read_bytes()).hexdigest() if (R/path).exists() else None})
(O/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
print(f'Prepared {len(manifest)} separate complete replacement files; installed source unchanged.')
