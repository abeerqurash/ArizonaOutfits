<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class CmsPageController extends Controller
{
    public function show(string $slug): View
    {
        $page=Page::published()->where('slug',$slug)->firstOrFail();
        if($page->render_mode==='custom'){
            $template='pages.custom.'.$page->blade_template;
            abort_unless($page->blade_template&&ViewFacade::exists($template),404,'The custom page template was not found.');
            return view($template,compact('page'));
        }
        return view('pages.show',compact('page'));
    }
}
