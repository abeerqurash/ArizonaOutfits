<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\View;

class BlogController extends Controller
{
    // Blog parent page (listing all posts)
    public function index()
    {
        $posts = Post::with('categories')->latest()->paginate(10); // adjust pagination as needed
        return view('blogs.index', compact('posts'));
    }

    // Single blog post page
    public function show($slug)
    {
        $post = Post::with('categories')->where('slug', $slug)->firstOrFail();

        // Get category IDs of this post
        $categoryIds = $post->categories->pluck('id');

        // Related posts (updated to 6 posts)
        $relatedPosts = Post::with('categories')
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $post->id)
            ->inRandomOrder()
            ->take(6)
            ->get();

        // Blade path
        $bladePath = 'blogs.posts.' . $slug;

        if (!View::exists($bladePath)) {
            abort(404, 'Post view not found.');
        }

        return view($bladePath, [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'title' => $post->meta_title ?? $post->title,
            'meta_description' => $post->meta_description
        ]);
    }
}
