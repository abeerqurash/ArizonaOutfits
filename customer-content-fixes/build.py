from pathlib import Path
import re,json,hashlib
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'customer-content-fixes';S=O/'_staged';files={}
def read(p):return files.get(p,(R/p).read_text(encoding='utf-8-sig') if (R/p).exists() else '')
def put(p,s):files[p]=s
def sub(p,a,b):
 s=read(p);assert a in s,(p,a);put(p,s.replace(a,b))
p='app/Http/Middleware/AdminMiddleware.php';sub(p,"                default => null,","                $request->routeIs('admin.customers.*') => 'customers.manage',\n                $request->routeIs('admin.reviews.*') => 'reviews.manage',\n                $request->routeIs('admin.posts.*', 'admin.categories.*') => 'content.manage',\n                default => null,")
p='resources/views/admin/partials/sidebar.blade.php';s=read(p)
for route,permission in [('customers','customers.manage'),('reviews','reviews.manage'),('posts','content.manage'),('categories','content.manage')]:
 m=re.search(r'<a\b[^>]*?href="\{\{\s*route\(\s*\'admin\.'+route+r'\.index\'[\s\S]*?</a>',s);assert m,route
 s=s[:m.start()]+f"@if(auth('admin')->user()?->hasAdminPermission('{permission}'))\n"+m[0]+'\n@endif'+s[m.end():]
put(p,s)
put('app/Services/CustomerSpendService.php','''<?php
namespace App\\Services;
use Illuminate\\Support\\Collection;

class CustomerSpendService
{
    public static function totals(Collection $orders): array
    {
        return $orders->where('payment_status', 'paid')->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy(fn ($order) => strtoupper($order->currency ?: config('shipping.currency', 'USD')))
            ->map(fn ($group) => round((float) $group->sum('total'), 2))->sortKeys()->all();
    }
    public static function format(array $totals): string
    {
        return collect($totals)->map(fn ($value, $currency) => $currency . ' ' . number_format($value, 2))->implode(' · ') ?: 'No paid orders';
    }
}
''')
p='app/Http/Controllers/Admin/CustomerController.php';s=read(p)
s=s.replace('use Illuminate\\View\\View;','use Illuminate\\View\\View;\nuse Illuminate\\Support\\Facades\\DB;\nuse App\\Services\\CustomerSpendService;')
a=s.index("            ->withCount('orders')");b=s.index('            ->when(',a)
s=s[:a]+'''            ->withCount(['orders' => fn ($query) => $query->withTrashed()])
            ->with(['orders' => fn ($query) => $query->withTrashed()->where('payment_status', 'paid')->whereNotIn('order_status', ['cancelled', 'refunded'])->select(['id', 'user_id', 'currency', 'total', 'payment_status', 'order_status'])])
'''+s[b:]
s=s.replace("        return view('admin.customers.index', compact('customers'));",'''        foreach ($customers as $listedCustomer) {
            $listedCustomer->paid_spend_by_currency = CustomerSpendService::totals($listedCustomer->orders);
        }
        return view('admin.customers.index', compact('customers'));''')
s=s.replace("'orders' => fn ($query) => $query->latest(),","'orders' => fn ($query) => $query->withTrashed()->latest(),")
a=s.index('        $totalSpent =');b=s.index('        return view(',a);s=s[:a]+'        $totalSpent = CustomerSpendService::totals($customer->orders);\n\n'+s[b:]
a=s.index('        if ($customer->orders()->exists())');b=s.index('        return redirect()',a)
s=s[:a]+'''        $deleted = DB::transaction(function () use ($customer): bool {
            $locked = User::query()->lockForUpdate()->findOrFail($customer->id);
            abort_if($locked->is_admin, 404);
            if ($locked->orders()->withTrashed()->exists()) return false;
            $locked->delete();
            return true;
        });
        if (!$deleted) {
            return back()->with('error', 'This customer has current or archived orders. Block the account instead of deleting its history.');
        }

'''+s[b:];put(p,s)
p='resources/views/admin/customers/index.blade.php';sub(p,"${{ number_format((float) ($customer->total_spent ?: 0), 2) }}","{{ \\App\\Services\\CustomerSpendService::format($customer->paid_spend_by_currency ?? []) }}")
p='resources/views/admin/customers/show.blade.php';sub(p,'${{ number_format((float) $totalSpent, 2) }}','{{ \\App\\Services\\CustomerSpendService::format($totalSpent) }}')
sub(p,'${{ number_format((float) $order->total, 2) }}',"{{ $order->currency ?: config('shipping.currency', 'USD') }} {{ number_format((float) $order->total, 2) }}")
s=read(p);s=s.replace("{{ $order->order_number ?: '#' . $order->id }}", "{{ $order->order_number ?: '#' . $order->id }}{{ $order->trashed() ? ' (Archived)' : '' }}");put(p,s)

p='app/Http/Controllers/ReviewController.php';sub(p,'        $user = Auth::user();', '''        abort_unless($product->status === 'active', 404);
        $user = Auth::guard('web')->user();
        abort_if($user && ($user->is_admin || $user->status !== 'active'), 403, 'This customer account cannot submit reviews.');''')
sub(p,"Rule::requiredIf(!$user),\n                'nullable',\n                'email',", "Rule::requiredIf(!$user || blank($user->email)),\n                'nullable',\n                'email',")
sub(p,"'email' => $user\n                ? $user->email", "'email' => $user\n                ? ($user->email ?: strtolower(trim($validated['email'])))")
p='resources/views/products/show.blade.php';s=read(p);a=s.index('class="product-review-form"');b=s.index('                                    @endauth',a)
part=s[a:b];part=part.replace('@auth',"@if(auth('web')->check() && filled(auth('web')->user()->email))").replace('auth()->user()',"auth('web')->user()").replace("old('name')", "old('name', auth('web')->user()?->name)");s=s[:a]+part+s[b:]
# Replace just this form's closing directive, preserving all other auth blocks.
end=s.index('                                    @endauth',a);s=s[:end]+s[end:].replace('@endauth','@endif',1);put(p,s)

put('app/Http/Middleware/CustomerAccountStatus.php','''<?php
namespace App\\Http\\Middleware;
use Closure;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Symfony\\Component\\HttpFoundation\\Response;

class CustomerAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('web')->user();
        if (!$customer || (!$customer->is_admin && $customer->status === 'active')) return $next($request);
        // Revoke only customer authentication; keep an independent admin session.
        Auth::guard('web')->logout();
        if ($request->hasSession()) $request->session()->regenerateToken();
        if ($request->is('admin', 'admin/*')) return $next($request);
        if ($request->expectsJson()) return response()->json(['message' => 'This customer account is currently disabled.'], 403);
        return redirect()->route('login')->withErrors(['email' => 'This customer account is currently disabled.']);
    }
}
''')
p='bootstrap/app.php';sub(p,"            SecurityHeadersMiddleware::class\n        );", "            SecurityHeadersMiddleware::class\n        );\n        $middleware->appendToGroup('web', \\App\\Http\\Middleware\\CustomerAccountStatus::class);")

p='app/Http/Controllers/Admin/PostController.php';s=read(p)
s=s.replace("'template' => ['nullable', 'string', 'max:255'],", "'template' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+(?:\\.[a-zA-Z0-9_-]+)*$/'],")
needle='        return $request->validate([';assert needle in s;s=s.replace(needle,'        $validated = $request->validate([',1)
a=s.index('    private function validatePost(');b=s.index('    private function normalizeCategoryIds',a);part=s[a:b]
closing=part.rfind('        ]);');assert closing!=-1
extra='''
        $status = $validated['status'];
        $templateName = !empty($validated['template']) ? trim($validated['template']) : ($post?->template ?: Str::slug($validated['title']));
        if (in_array($status, [Post::STATUS_PUBLISHED, Post::STATUS_SCHEDULED], true)
            && !View::exists('blogs.posts.' . $templateName)) {
            throw ValidationException::withMessages(['template' => 'Create the custom article Blade file in resources/views/blogs/posts before publishing or scheduling. Save as Draft until the file is ready.']);
        }
        return $validated;
'''
part=part[:closing+11]+extra+part[closing+11:];s=s[:a]+part+s[b:];put(p,s)

p='app/Http/Controllers/Admin/CategoryController.php';s=read(p);needle="        if (!str_starts_with($normalized, 'categories/')) {";assert needle in s
s=s.replace(needle,"        if (str_contains($normalized, chr(92)) || preg_match('~(?:^|/)\\.{1,2}(?:/|$)|[:?#]~', $normalized)) return;\n\n"+needle)
needle="        if (Storage::disk('public')->exists($normalized)) {";assert needle in s
s=s.replace(needle,'''        foreach (Category::query()->whereNotNull('image')->pluck('image') as $usedPath) {
            $used = ltrim((string) $usedPath, '/');
            if (str_starts_with($used, 'storage/')) $used = substr($used, 8);
            if ($used === $normalized) return;
        }
'''+needle);put(p,s)

manifest=[]
p='resources/views/admin/posts/_form.blade.php';s=read(p)
s=s.replace('Legacy Template Name','Custom Article Template').replace('Preserved for compatibility with existing blog records.','Use the file name from resources/views/blogs/posts, without .blade.php. Published and scheduled articles require this file.')
needle="@error('status')<span class=\"az-error\">{{ $message }}</span>@enderror";assert needle in s
s=s.replace(needle,needle+"\n                    @error('template')<span class=\"az-error\">{{ $message }}</span>@enderror")
put(p,s)
for n,(path,s) in enumerate(files.items(),1):
 p=S/path;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8');name=f'{n:02d}_{Path(path).name}.txt';(O/name).write_text(s,encoding='utf-8')
 manifest.append({'txt':name,'relative':path,'destination':str(R/path),'new':not (R/path).exists(),'original_sha256':hashlib.sha256((R/path).read_bytes()).hexdigest() if (R/path).exists() else None})
(O/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
print(f'Prepared {len(manifest)} complete TXT files; installed files unchanged.')
