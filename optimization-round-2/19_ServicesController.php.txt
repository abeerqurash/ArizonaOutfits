<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index()
    {
        abort_unless(view()->exists('services'),404);
        return view('services');
    }

    public function show($slug)
    {
        if (!view()->exists("services.$slug")) {
            abort(404);
        }

        return view("services.$slug");
    }
}