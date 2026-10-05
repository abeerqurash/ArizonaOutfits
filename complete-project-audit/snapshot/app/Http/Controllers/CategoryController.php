<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display all blog categories.
     */
    public function index()
    {
        $categories = Category::query()
            ->withCount([
                'posts as published_posts_count' => function ($query) {
                    $query->published();
                },
            ])
            ->ordered()
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Display one blog category and its publicly available posts.
     */
    public function show($slug)
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = $category->posts()
            ->with([
                'categories',
                'primaryCategory',
                'author',
            ])
            ->published()
            ->orderByRaw('COALESCE(published_at, scheduled_at) DESC')
            ->orderByDesc('id')
            ->paginate(10);

        return view('categories.show', compact('category', 'posts'));
    }
}
