<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AbeerKhanController extends Controller
{
    public function index()
    {
        abort_unless(view()->exists('abeerkhan'),404);
        return view('abeerkhan', [
            'title' => 'Abeer Khan',
            'meta_description' => 'Learn more about our company.',
            'robots' => 'index, follow'
        ]);
    }
}
