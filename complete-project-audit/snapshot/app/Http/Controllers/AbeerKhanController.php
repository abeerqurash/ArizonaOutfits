<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbeerKhanController extends Controller
{
    public function index()
    {
        return view('abeerkhan', [
            'title' => 'Abeer Khan',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow'
        ]);
    }
}
