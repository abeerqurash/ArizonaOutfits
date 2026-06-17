<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class privacyPolicyController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();

        return view('privacy-policy', [
            'title' => 'Privacy Policy',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow',
            'posts' => $posts
        ]);
    }
}