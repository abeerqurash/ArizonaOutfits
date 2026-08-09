<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Services\CmsContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPageController extends AdminController
{
    public function index(Request $request): View
    {
        $search=trim($request->string('search')->value()); $status=$request->string('status')->value();
        $pages=Page::query()->with('editor:id,name')->when($search!=='',fn($q)=>$q->where(fn($x)=>$x->where('title','like',"%{$search}%")->orWhere('slug','like',"%{$search}%")))->when(in_array($status,['draft','published'],true),fn($q)=>$q->where('status',$status))->latest('updated_at')->paginate(25)->withQueryString();
        $stats=['all'=>Page::count(),'published'=>Page::where('status','published')->count(),'draft'=>Page::where('status','draft')->count()];
        return view('admin.pages.index',compact('pages','stats'));
    }

    public function create(): View { return view('admin.pages.create',['page'=>new Page(['render_mode'=>'editor']),'customTemplates'=>$this->customTemplates()]); }

    public function store(Request $request,CmsContentSanitizer $sanitizer): RedirectResponse
    {
        $data=$this->validated($request); $data['slug']=$this->slug($data['slug']??null,$data['title']); $data['content']=$sanitizer->clean($data['content']??'');
        $data['created_by']=$request->user()->id; $data['updated_by']=$request->user()->id; $data['published_at']=$data['status']==='published'?now():null;
        $page=Page::create($data);
        return redirect()->route('admin.pages.edit',$page)->with('success','CMS page created.');
    }

    public function edit(Page $page): View { $customTemplates=$this->customTemplates(); return view('admin.pages.edit',compact('page','customTemplates')); }

    public function update(Request $request,Page $page,CmsContentSanitizer $sanitizer): RedirectResponse
    {
        $data=$this->validated($request,$page); $data['slug']=$this->slug($data['slug']??null,$data['title'],$page); $data['content']=$sanitizer->clean($data['content']??''); $data['updated_by']=$request->user()->id;
        $data['published_at']=$data['status']==='published'?($page->published_at??now()):null; $page->update($data);
        return back()->with('success','CMS page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete(); return redirect()->route('admin.pages.index')->with('success','CMS page deleted.');
    }

    private function validated(Request $request,?Page $page=null): array
    {
        return $request->validate([
            'title'=>['required','string','max:255'],
            'slug'=>['nullable','string','max:255',Rule::unique('pages','slug')->ignore($page?->id)],
            'excerpt'=>['nullable','string','max:1000'],
            'render_mode'=>['required','in:editor,custom'],
            'blade_template'=>['nullable','required_if:render_mode,custom','string',Rule::in(array_keys($this->customTemplates()))],
            'content'=>['nullable','required_if:render_mode,editor','string','max:200000'],
            'status'=>['required','in:draft,published'],'template'=>['required','in:default,wide,minimal'],
            'meta_title'=>['nullable','string','max:255'],'meta_description'=>['nullable','string','max:500'],
        ]);
    }

    private function slug(?string $slug,string $title,?Page $page=null): string
    {
        $base=Str::slug($slug?:$title)?:'page'; $candidate=$base; $suffix=2;
        while(Page::query()->where('slug',$candidate)->when($page,fn($q)=>$q->where('id','!=',$page->id))->exists()) $candidate=$base.'-'.$suffix++;
        return $candidate;
    }

    private function customTemplates(): array
    {
        $directory=resource_path('views/pages/custom');
        if(!File::isDirectory($directory))return [];
        return collect(File::files($directory))->filter(fn($file)=>str_ends_with($file->getFilename(),'.blade.php'))->mapWithKeys(function($file):array{$name=str_replace('.blade.php','',$file->getFilename());return[$name=>Str::headline($name)];})->sort()->all();
    }
}
