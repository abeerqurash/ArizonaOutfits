<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function index()
    {
        return view('projects');
    }

    public function show($slug)
    {
        if (!view()->exists("projects.$slug")) {
            abort(404);
        }

        return view("projects.$slug");
    }
}