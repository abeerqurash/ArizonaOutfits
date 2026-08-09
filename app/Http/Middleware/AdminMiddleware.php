<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Your account has been disabled.');
        }

        if (!$user->is_admin) {
            abort(403, 'You are not authorized to access the administration area.');
        }

        $requiredPermission = $this->requiredPermission(
            (string) optional($request->route())->getName()
        );

        if (
            $requiredPermission !== null
            && !$user->hasAdminPermission($requiredPermission)
        ) {
            abort(
                403,
                'Your administrator role does not have permission to access this section.'
            );
        }

        return $next($request);
    }

    private function requiredPermission(string $routeName): ?string
    {
        return match (true) {
            $routeName === 'admin.dashboard' =>
                'dashboard.view',

            Str::is('admin.analytics.*', $routeName) =>
                'dashboard.view',

            Str::is('admin.orders.*', $routeName) =>
                'orders.manage',

            Str::is([
                'admin.products.*',
                'admin.product-categories.*',
                'admin.product-tags.*',
                'admin.product-options.*',
            ], $routeName) => 'products.manage',

            Str::is('admin.coupons.*', $routeName) =>
                'coupons.manage',

            Str::is([
                'admin.inventory-*',
                'admin.stock-valuation.*',
                'admin.reorder-dashboard.*',
            ], $routeName) => 'inventory.manage',

            Str::is('admin.purchase-orders.*', $routeName) =>
                'purchase-orders.manage',

            Str::is('admin.suppliers.*', $routeName) =>
                'suppliers.manage',

            Str::is('admin.customers.*', $routeName) =>
                'customers.manage',

            Str::is('admin.reviews.*', $routeName) =>
                'reviews.manage',

            Str::is([
                'admin.posts.*',
                'admin.categories.*',
                'admin.pages.*',
                'admin.navigation-menus.*',
            ], $routeName) => 'content.manage',

            Str::is('admin.settings.*', $routeName) =>
                'settings.manage',

            Str::is([
                'admin.admin-users.*',
                'admin.admin-roles.*',
            ], $routeName) => 'admin-users.manage',

            Str::is('admin.audit-logs.*', $routeName) =>
                'audit-logs.view',

            Str::is('admin.notifications.*', $routeName) =>
                'notifications.manage',

            Str::is('admin.backups.*', $routeName) =>
                'backups.manage',

            Str::is('admin.email-templates.*', $routeName) =>
                'settings.manage',

            default => null,
        };
    }
}
