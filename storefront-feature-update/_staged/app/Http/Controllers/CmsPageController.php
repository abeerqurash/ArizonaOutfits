<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class CmsPageController extends Controller
{
    public function resolve(string $slug): View|\Illuminate\Http\RedirectResponse
    {
        if(Page::published()->where('slug',$slug)->exists())return $this->show($slug);
        $response = app(BlogController::class)->show($slug);
        if ($response instanceof \Illuminate\Http\RedirectResponse) return $response;
        return redirect()->route('blog-show', ['slug' => $slug], 301);
    }

    public function show(string $slug): View|\Illuminate\Http\RedirectResponse
    {
        if(request()->routeIs('pages.show')){Page::published()->where('slug',$slug)->firstOrFail();return redirect(url('/'.$slug),301);}
        $page=Page::published()->where('slug',$slug)->firstOrFail();
        if($page->render_mode==='custom'){
            $template='pages.custom.'.$page->blade_template;
            abort_unless($page->blade_template&&ViewFacade::exists($template),404,'The custom page template was not found.');
            return view($template,compact('page'));
        }
        return view('pages.show',compact('page'));
    }
}
