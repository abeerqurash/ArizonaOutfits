<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index()
    {
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