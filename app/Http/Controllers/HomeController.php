<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $posts = Post::with('categories')
                    ->latest()
                    ->take(6)   // show 6 posts
                    ->get();

        return view('home', [
            'title' => 'Novahub',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow',
            'posts' => $posts
        ]);
    }
}