<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Services\NavigationMenuService;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(10)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());

        RateLimiter::for('admin', function (Request $request): Limit {
            return Limit::perMinute(180)->by(
                (string) ($request->user()?->id ?? $request->ip())
            );
        });

        RateLimiter::for('authentication', function (Request $request): array {
            return [
                Limit::perMinute(20)->by($request->ip()),
                Limit::perMinute(5)->by(strtolower((string) $request->input('email')) . '|' . $request->ip()),
            ];
        });

        View::composer('*', function ($view): void {
            if (request()->is('admin', 'admin/*')) {
                return;
            }

            $view->with('popularCategories', Category::withCount('posts')
                ->has('posts')
                ->orderByDesc('posts_count')
                ->take(10)
                ->get());

            $view->with('latestPosts', Post::latestPosts(3)->get());
        });

        View::composer('admin.layouts.sidebar', function ($view): void {
            $variantCount = \App\Models\ProductVariant::query()
                ->whereRaw('GREATEST(COALESCE(stock, 0), 0) <= GREATEST(COALESCE(reorder_point, 5), 0)')
                ->count();

            $simpleProductCount = Product::query()
                ->whereDoesntHave('variants')
                ->whereRaw('GREATEST(COALESCE(stock, 0), 0) <= GREATEST(COALESCE(reorder_point, 5), 0)')
                ->count();

            $reorderRequiredCount = $variantCount + $simpleProductCount;

            $view->with('reorderRequiredCount', $reorderRequiredCount);
        });

        View::composer('admin.partials.topbar', function ($view): void {
            $user = auth()->user();
            $latestAdminNotifications = collect();
            $unreadAdminNotificationCount = 0;

            if ($user?->is_admin && Schema::hasTable('notifications')) {
                $latestAdminNotifications = $user->notifications()
                    ->latest()
                    ->limit(6)
                    ->get();
                $unreadAdminNotificationCount = $user->unreadNotifications()->count();
            }

            $view->with(compact(
                'latestAdminNotifications',
                'unreadAdminNotificationCount'
            ));
        });

        View::composer(['partials.header','partials.footer'],function($view):void{
            $location=$view->getName()==='partials.header'?'header':'footer';
            $view->with('navigationMenuItems',app(NavigationMenuService::class)->items($location));
        });
    }
}
