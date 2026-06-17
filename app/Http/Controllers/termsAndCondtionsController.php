<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class termsAndCondtionsController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();

        return view('terms-and-conditions', [
            'title' => 'Terms And Conditions',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow',
            'posts' => $posts
        ]);
    }
}