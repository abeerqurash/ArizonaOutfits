<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Services\NavigationMenuService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ([
            \App\Models\Order::class, \App\Models\Review::class,
            \App\Models\User::class, \App\Models\InventoryAlert::class,
            \App\Models\InventoryHistory::class, \App\Models\AdminBackup::class,
            \App\Models\AdminAuditLog::class,
        ] as $bellModel) {
            $bellModel::observe(\App\Observers\AdminBellObserver::class);
        }

        Password::defaults(fn () => Password::min(10)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());

        /*
        |--------------------------------------------------------------------------
        | Password reset URL separation
        |--------------------------------------------------------------------------
        |
        | Laravel's ResetPassword notification is shared by all authenticatable
        | models. Admins must be sent to the dedicated admin reset route while
        | customers continue using the normal customer reset route.
        |
        */
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $route = $notifiable instanceof Admin
                ? 'admin.password.reset'
                : 'password.reset';

            return URL::route($route, [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        RateLimiter::for('admin', function (Request $request): Limit {
            return Limit::perMinute(180)->by(
                (string) ($request->user('admin')?->id ?? $request->ip())
            );
        });

        RateLimiter::for('authentication', function (Request $request): array {
            return [
                Limit::perMinute(20)->by($request->ip()),
                Limit::perMinute(5)->by(
                    strtolower((string) $request->input('email')) . '|' . $request->ip()
                ),
            ];
        });

        // These views actually display shared blog lists. Reuse fallback data
        // within this request and preserve lists supplied by their controller.
        View::composer([
            'home', 'about', 'terms-and-conditions', 'privacy-policy',
            'blogs.posts.*', 'partials.latest-posts',
            'blogs.partials.article-author-latest',
        ], function ($view): void {
            if (request()->is('admin', 'admin/*')) {
                return;
            }

            $data = $view->getData();
            $request = request();

            if (!array_key_exists('popularCategories', $data)) {
                $key = 'arizona.public_blog.popular_categories';
                if (!$request->attributes->has($key)) {
                    $categories = Category::query()
                        ->withCount(['posts as published_posts_count' => fn ($query) => $query->published()])
                        ->whereHas('posts', fn ($query) => $query->published())
                        ->orderByDesc('published_posts_count')
                        ->orderBy('title')
                        ->take(10)
                        ->get();
                    // Retain the legacy count name for older public templates.
                    $categories->each(fn ($category) => $category->setAttribute(
                        'posts_count', $category->published_posts_count
                    ));
                    $request->attributes->set($key, $categories);
                }
                $view->with('popularCategories', $request->attributes->get($key));
            }

            if (!array_key_exists('latestPosts', $data)) {
                $key = 'arizona.public_blog.latest_posts';
                if (!$request->attributes->has($key)) {
                    $request->attributes->set($key, Post::query()
                        ->with(['categories', 'primaryCategory', 'author'])
                        ->published()
                        ->latestPosts()
                        ->take(3)
                        ->get());
                }
                $view->with('latestPosts', $request->attributes->get($key));
            }
        });

        View::composer(['admin.partials.sidebar', 'admin.layouts.sidebar'], function ($view): void {
            $variantCount = \App\Models\ProductVariant::query()
                ->whereRaw(
                    'GREATEST(COALESCE(stock, 0), 0) <= GREATEST(COALESCE(reorder_point, 5), 0)'
                )
                ->count();

            $simpleProductCount = Product::query()
                ->whereDoesntHave('variants')
                ->whereRaw(
                    'GREATEST(COALESCE(stock, 0), 0) <= GREATEST(COALESCE(reorder_point, 5), 0)'
                )
                ->count();

            $reorderRequiredCount = $variantCount + $simpleProductCount;

            $view->with('reorderRequiredCount', $reorderRequiredCount);
            $view->with('activeInventoryAlertCount', Schema::hasTable('inventory_alerts')
                ? \App\Models\InventoryAlert::where('status', 'active')->count() : 0);
        });

        View::composer('admin.partials.topbar', function ($view): void {
            $admin = auth('admin')->user();

            $latestAdminNotifications = collect();
            $unreadAdminNotificationCount = 0;

            if ($admin instanceof Admin && Schema::hasTable('notifications')) {
                $latestAdminNotifications = $admin->notifications()
                    ->latest()
                    ->limit(6)
                    ->get();

                $unreadAdminNotificationCount = $admin->unreadNotifications()->count();
            }

            $view->with(compact(
                'latestAdminNotifications',
                'unreadAdminNotificationCount'
            ));
        });

        View::composer(
            ['partials.header', 'partials.footer'],
            function ($view): void {
                $location = $view->getName() === 'partials.header'
                    ? 'header'
                    : 'footer';

                $view->with(
                    'navigationMenuItems',
                    app(NavigationMenuService::class)->items($location)
                );
            }
        );
    }
}
