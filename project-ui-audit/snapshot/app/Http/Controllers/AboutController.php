<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        return view('about', [
            'title' => 'About',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow'
        ]);
    }
}