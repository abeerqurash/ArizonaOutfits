<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\View;

class CategoryController extends Controller
{

    public function index()
    {
        // Get all categories
        $categories = Category::all();

        // Optionally, you can only get categories with posts
        // $categories = Category::has('posts')->get();

        return view('categories.index', compact('categories'));
    }
    public function show($slug)
    {
        // Get the category
        $category = Category::where('slug', $slug)->firstOrFail();

        // Get all posts for this category
        $posts = $category->posts()->latest()->paginate(10);

        // Dynamic Blade path per category slug
        $bladePath = 'categories.' . $slug;

        // Check if the Blade exists
        if (!View::exists($bladePath)) {
            abort(404, 'Category view not found.');
        }

        return view($bladePath, compact('category', 'posts'));
    }
}
