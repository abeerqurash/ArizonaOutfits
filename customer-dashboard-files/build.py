from pathlib import Path
import json, hashlib, shutil

root=Path(__file__).resolve().parent.parent
out=Path(__file__).resolve().parent
stage=out/'_staged'
destinations=[]
def put(path, data):
    target=stage/path
    target.parent.mkdir(parents=True,exist_ok=True)
    target.write_bytes(data.encode('utf-8') if isinstance(data,str) else data)
    if path not in destinations: destinations.append(path)

# One combined snapshot: the newest bell versions override the preceding admin set.
for package in ['customer-admin-sync-files','bell-notification-files']:
    for entry in json.loads((root/package/'manifest.json').read_text()):
        put(entry['destination'],(root/package/entry['file']).read_bytes())

paths=[
 'app/Http/Controllers/CustomerDashboardController.php',
 'app/Http/Controllers/Customer/LoginMethodController.php',
 'app/Http/Controllers/FavoriteController.php',
 'app/Http/Controllers/ReviewController.php',
 'app/Http/Controllers/OrderTrackingController.php',
 'app/Http/Controllers/Admin/CustomerController.php',
 'app/Http/Middleware/CustomerAccountStatus.php',
 'app/Services/CustomerSpendService.php',
 'app/Services/CustomerLoginSecurity.php',
 'app/Services/PasswordSecurityService.php',
 'app/Models/User.php', 'app/Models/CustomerEmailIdentity.php',
 'app/Http/Requests/ProfileUpdateRequest.php',
 'app/Http/Requests/Auth/LoginRequest.php',
 'app/Http/Controllers/Auth/AuthenticatedSessionController.php',
 'routes/auth.php',
 'resources/views/admin/orders/invoice.blade.php',
 'resources/views/admin/orders/invoice-pdf.blade.php',
 'resources/views/orders/track.blade.php',
 'resources/views/favorites/index.blade.php',
]
paths += [str(p.relative_to(root)).replace('\\','/') for directory in ['resources/views/customer','resources/views/profile'] for p in (root/directory).rglob('*.blade.php')]
for path in paths:
    data=(root/path).read_bytes()
    if path=='app/Http/Controllers/FavoriteController.php':
        data=data.decode('utf-8-sig').replace('auth()->id()', "auth('web')->id()")
    put(path,data)

path='app/Http/Controllers/CustomerDashboardController.php'
s=(stage/path).read_text(encoding='utf-8-sig')
s=s.replace("        $ordersQuery = $request->user()->orders()->withTrashed();", "        $customer = $this->customer($request);\n        $ordersQuery = $customer->orders()->withTrashed();")
s=s.replace("                    'out_for_delivery',\n",'',1)
s=s.replace("->where('order_status', 'shipped')", "->whereIn('order_status', ['shipped', 'out_for_delivery'])",1)
s=s.replace("'processing' => (clone $ordersQuery)", "'processing' => (clone $ordersQuery)->whereNull('deleted_at')")
s=s.replace("'shipped' => (clone $ordersQuery)", "'shipped' => (clone $ordersQuery)->whereNull('deleted_at')")
s=s.replace("        $recentOrders = $request->user()", "        $paidSpend = \\App\\Services\\CustomerSpendService::totals((clone $ordersQuery)\n            ->where('payment_status', 'paid')\n            ->whereNotIn('order_status', ['cancelled', 'refunded'])\n            ->get(['id', 'user_id', 'currency', 'total', 'payment_status', 'order_status']));\n        $stats['pending_payment'] = (clone $ordersQuery)->whereNull('deleted_at')\n            ->whereIn('payment_status', ['pending', 'partially_paid'])\n            ->whereNotIn('order_status', ['cancelled', 'refunded'])->count();\n\n        $recentOrders = $customer")
s=s.replace("compact('stats', 'recentOrders')", "compact('stats', 'recentOrders', 'paidSpend')")
needle="        $filters = $request->validate(["
s=s.replace(needle,"        $customer = $this->customer($request);\n"+needle,1)
s=s.replace("            'search' => ['nullable', 'string', 'max:100'],", "            'search' => ['nullable', 'string', 'max:100'],\n            'payment_status' => ['nullable', 'in:pending,paid,partially_paid,completed,succeeded,failed,declined,cancelled,refunded'],")
s=s.replace("        $orders = $request->user()", "        $orders = $customer")
s=s.replace("            ->latest()\n            ->paginate(12)", "            ->when(filled($filters['payment_status'] ?? null),\n                fn (Builder $query) => $query->where('payment_status', $filters['payment_status']))\n            ->latest()\n            ->paginate(12)")
s=s.replace("        return $request->user()\n            ->orders()", "        return $this->customer($request)\n            ->orders()")
idx=s.rfind('\n}')
s=s[:idx]+'''
    private function customer(Request $request): \\App\\Models\\User
    {
        $customer = $request->user('web');
        abort_unless($customer instanceof \\App\\Models\\User, 401);
        abort_if($customer->is_admin || $customer->is_super_admin || $customer->status !== 'active', 403);
        return $customer;
    }
'''+s[idx:]
put(path,s)

path='resources/views/customer/dashboard.blade.php'
s=(stage/path).read_text(encoding='utf-8-sig').replace('auth()->user()->name', "auth('web')->user()->name")
s=s.replace('<small>Shipped</small>','<small>In transit</small>')
needle='<section class="customer-panel">'
addition='''<section class="customer-panel customer-payment-summary">
    <header class="customer-panel-heading"><div><span>Payment overview</span><h3>Paid purchases</h3></div></header>
    <div class="customer-summary-body">
        @forelse ($paidSpend as $currency => $amount)
            <div><small>{{ $currency }}</small><strong>{{ number_format($amount, 2) }}</strong></div>
        @empty
            <p>No paid purchases yet.</p>
        @endforelse
        <a href="{{ route('customer.orders.index', ['payment_status' => 'pending']) }}">{{ number_format($stats['pending_payment']) }} order(s) awaiting full payment</a>
    </div>
</section>

'''
# Link shows all awaiting-full-payment statuses by using a dedicated filter value below.
addition=addition.replace("['payment_status' => 'pending']", "['payment_status' => 'awaiting_payment']")
s=s.replace(needle,addition+needle,1)
s=s.replace('<th>Status</th><th></th>', '<th>Payment</th><th>Status</th><th></th>')
s=s.replace('<td><span class="customer-status {{ $order->order_status }}">', '<td><span class="customer-status {{ $order->payment_status }}">{{ str($order->payment_status ?: \'pending\')->headline() }}</span></td><td><span class="customer-status {{ $order->order_status }}">')
put(path,s)

# The payment-summary link and order query use exactly the same definition.
path='app/Http/Controllers/CustomerDashboardController.php'
s=(stage/path).read_text().replace('in:pending,paid,partially_paid,completed,succeeded,failed,declined,cancelled,refunded','in:awaiting_payment,pending,paid,partially_paid,completed,succeeded,failed,declined,cancelled,refunded')
s=s.replace("fn (Builder $query) => $query->where('payment_status', $filters['payment_status']))", "function (Builder $query) use ($filters): void {\n                    if ($filters['payment_status'] === 'awaiting_payment') {\n                        $query->whereNull('deleted_at')->whereIn('payment_status', ['pending', 'partially_paid'])\n                            ->whereNotIn('order_status', ['cancelled', 'refunded']);\n                    } else {\n                        $query->where('payment_status', $filters['payment_status']);\n                    }\n                })")
put(path,s)

path='resources/views/customer/orders/index.blade.php'
s=(stage/path).read_text(encoding='utf-8-sig')
needle='        <button type="submit">'
addition='''        <label>
            <span>Payment status</span>
            <select name="payment_status">
                <option value="">All payments</option>
                <option value="awaiting_payment" @selected(request('payment_status') === 'awaiting_payment')>Awaiting full payment</option>
                @foreach (['pending','paid','partially_paid','completed','succeeded','failed','declined','cancelled','refunded'] as $paymentStatus)
                    <option value="{{ $paymentStatus }}" @selected(request('payment_status') === $paymentStatus)>{{ str($paymentStatus)->headline() }}</option>
                @endforeach
            </select>
        </label>

'''
s=s.replace(needle,addition+needle,1).replace("['search', 'status']", "['search', 'status', 'payment_status']")
put(path,s)

for path in ['resources/views/customer/partials/sidebar.blade.php','resources/views/customer/partials/navigation.blade.php']:
    s=(stage/path).read_text(encoding='utf-8-sig').replace('Profile & Security','Profile')
    if path.endswith('sidebar.blade.php'):
        needle='            </nav>'
        add='''                <a href="{{ route('customer.security') }}" class="admin-menu-link {{ request()->routeIs('customer.security*') ? 'active' : '' }}"><span class="admin-menu-icon"><i class="fa-solid fa-shield-halved"></i></span><span class="admin-menu-text">Login & Security</span></a>
                <a href="{{ route('favorites.index') }}" class="admin-menu-link {{ request()->routeIs('favorites.*') ? 'active' : '' }}"><span class="admin-menu-icon"><i class="fa-regular fa-heart"></i></span><span class="admin-menu-text">My Favorites</span></a>
'''
    else:
        needle='    <form method="POST"'
        add='''    <a href="{{ route('customer.security') }}"><i class="fa-solid fa-shield-halved"></i> Login & Security</a>
    <a href="{{ route('favorites.index') }}"><i class="fa-regular fa-heart"></i> My Favorites</a>
'''
    s=s.replace(needle,add+needle,1);put(path,s)

path='resources/views/customer/partials/topbar.blade.php'
s=(stage/path).read_text(encoding='utf-8-sig').replace('$topbarUser = auth()->user();', "$topbarUser = auth('web')->user();")
needle='                <a href="{{ route(\'home-page\') }}">'
s=s.replace(needle,'''                <a href="{{ route('customer.security') }}"><i class="fa-solid fa-shield-halved"></i>Login & Security</a>
                <a href="{{ route('favorites.index') }}"><i class="fa-regular fa-heart"></i>My Favorites</a>
'''+needle,1);put(path,s)

path='resources/views/customer/partials/styles.blade.php'
s=(stage/path).read_text(encoding='utf-8-sig').replace('</style>', '''.customer-payment-summary{margin-bottom:22px}.customer-summary-body{display:flex;flex-wrap:wrap;align-items:center;gap:20px;padding:20px}.customer-summary-body small,.customer-summary-body strong{display:block}.customer-summary-body small{color:#64748b;font-size:11px}.customer-summary-body strong{font-size:21px}.customer-summary-body>a{margin-left:auto;color:#0f766e;font-size:13px}.customer-status.out_for_delivery{background:#dbeafe;color:#1d4ed8}.customer-filters{flex-wrap:wrap}
@media(max-width:760px){.customer-summary-body{align-items:flex-start;flex-direction:column}.customer-summary-body>a{margin-left:0}}
</style>''')
put(path,s)

manifest=[]
for i,path in enumerate(destinations,1):
    name=f'{i:02d}_{Path(path).name}.txt'
    data=(stage/path).read_bytes()
    (out/name).write_bytes(data)
    manifest.append({'file':name,'destination':path,'sha256':hashlib.sha256(data).hexdigest()})
(out/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
print(f'Prepared {len(manifest)} separate customer + latest admin/bell files.')
