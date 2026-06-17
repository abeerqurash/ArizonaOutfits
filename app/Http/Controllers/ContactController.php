<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact', [
            'title' => 'Contact Us',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow'
        ]);
    }
}