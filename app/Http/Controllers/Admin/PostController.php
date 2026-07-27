<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends AdminController
{
    public function index()
    {
        $posts = Post::with('categories')->latest()->paginate(10);

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::orderBy('title')->get();

        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'expert' => 'nullable|string',
            'feature_image' => 'nullable|string|max:255',
            'template' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        if (empty($data['template'])) {
            $data['template'] = $data['slug'];
        }

        $post = Post::create($data);

        $post->categories()->sync($request->categories ?? []);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post)
    {
        $categories = Category::orderBy('title')->get();

        $selectedCategories = $post->categories()->pluck('categories.id')->toArray();

        return view('admin.posts.edit', compact('post', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'expert' => 'nullable|string',
            'feature_image' => 'nullable|string|max:255',
            'template' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if (empty($data['template'])) {
            $data['template'] = $data['slug'];
        }

        $post->update($data);

        $post->categories()->sync($request->categories ?? []);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->categories()->detach();

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}