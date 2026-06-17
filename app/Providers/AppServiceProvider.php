<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use App\Models\Post;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $popularCategories = Category::withCount('posts')
                ->having('posts_count', '>', 0)
                ->orderByDesc('posts_count')
                ->take(10)
                ->get();

            $view->with('popularCategories', $popularCategories);

            $latestPosts = Post::latestPosts(3)->get();

            $view->with('latestPosts', $latestPosts);
        });
    }
}
