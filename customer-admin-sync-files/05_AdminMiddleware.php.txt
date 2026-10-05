<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ?string $permission = null
    ): Response {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        if (!$admin->isActive()) {
            /*
             * Remove ONLY the disabled administrator authentication.
             *
             * Customer authentication may intentionally coexist in the same
             * Laravel browser session through the separate "web" guard, so
             * the complete session must not be invalidated here.
             */
            Auth::guard('admin')->logout();

            /*
             * Rotate the CSRF token after removing the disabled admin while
             * preserving unrelated session data, including customer login.
             */
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'email' => 'This administrator account is currently disabled.',
                ]);
        }

        if (blank($permission)) {
            $permission = match (true) {
                $request->routeIs('admin.inventory-alerts.*', 'admin.inventory-history.*', 'admin.inventory-reports.*', 'admin.stock-valuation.*', 'admin.reorder-dashboard.*', 'admin.products.inventory.*') => 'inventory.manage',
                $request->routeIs('admin.purchase-orders.*') => 'purchase-orders.manage',
                $request->routeIs('admin.suppliers.*') => 'suppliers.manage',
                $request->routeIs('admin.orders.*', 'admin.payment-verifications.*') => 'orders.manage',
                $request->routeIs('admin.products.*', 'admin.product-categories.*', 'admin.product-tags.*') => 'products.manage',
                $request->routeIs('admin.coupons.*') => 'coupons.manage',
                $request->routeIs('admin.customers.*') => 'customers.manage',
                $request->routeIs('admin.reviews.*') => 'reviews.manage',
                $request->routeIs('admin.posts.*', 'admin.categories.*') => 'content.manage',
                $request->routeIs('admin.settings.*', 'admin.email-templates.*') => 'settings.manage',
                $request->routeIs('admin.pages.*', 'admin.navigation-menus.*') => 'content.manage',
                $request->routeIs('admin.analytics.*') => 'dashboard.view',
                $request->routeIs('admin.admin-users.*', 'admin.admin-roles.*') => 'admin-users.manage',
                $request->routeIs('admin.notifications.*') => 'notifications.manage',
                $request->routeIs('admin.audit-logs.*') => 'audit-logs.view',
                $request->routeIs('admin.backups.*') => 'backups.manage',
                default => null,
            };
        }

        if (
            filled($permission)
            && !$admin->hasAdminPermission($permission)
        ) {
            abort(
                403,
                'You do not have permission to perform this administrator action.'
            );
        }

        return $next($request);
    }
}
