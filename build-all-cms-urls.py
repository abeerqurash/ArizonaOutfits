from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'all-cms-clean-url-files';o.mkdir(exist_ok=True)
files=[]
def save(p,s):
 n=f'{len(files)+1:02d}_'+Path(p).name+'.txt';(o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8');files.append((n,p))
s=(r/'routes/web.php').read_text(encoding='utf-8-sig');s=s.replace("[PrivacyPolicyController::class, 'index']","fn () => app(CmsPageController::class)->show('privacy-policy')",1)
s=s.replace("[TermsAndCondtionsController::class, 'index']","fn () => app(CmsPageController::class)->show('terms-and-conditions')",1)
s=s.replace("[BlogController::class, 'show']","[CmsPageController::class, 'resolve']",1);save('routes/web.php',s)
s=(r/'app/Models/Page.php').read_text(encoding='utf-8-sig');a=s.index('    public function getPublicUrlAttribute()');s=s[:a]+'''    public function getPublicUrlAttribute(): string
    {
        return url('/'.$this->slug);
    }
}
''';save('app/Models/Page.php',s)
s=(r/'app/Http/Controllers/CmsPageController.php').read_text(encoding='utf-8-sig');a=s.find("        if($slug==='terms-and-conditions'");b=s.index("        $page=Page::published()",a if a>=0 else 0)
if a>=0:s=s[:a]+s[b:]
s=s.replace("        $page=Page::published()", "        if(request()->routeIs('pages.show')){Page::published()->where('slug',$slug)->firstOrFail();return redirect(url('/'.$slug),301);}\n        $page=Page::published()",1)
pos=s.index('    public function show')
s=s[:pos]+'''    public function resolve(string $slug): View|\\Illuminate\\Http\\RedirectResponse
    {
        if(Page::published()->where('slug',$slug)->exists())return $this->show($slug);
        return app(BlogController::class)->show($slug);
    }

'''+s[pos:];save('app/Http/Controllers/CmsPageController.php',s)
s=(r/'app/Http/Controllers/Admin/AdminPageController.php').read_text(encoding='utf-8-sig');s=s.replace("        $candidate = $base;",'''        $reserved=collect(\\Illuminate\\Support\\Facades\\Route::getRoutes())->map(fn($route)=>trim($route->uri(),'/'))->filter(fn($uri)=>$uri!==''&&!str_contains($uri,'/')&&!str_contains($uri,'{'))->diff(['privacy-policy','terms-and-conditions'])->all();
        $candidate = $base;''',1)
s=s.replace("                ->exists()\n        ) {", "                ->exists()\n            || in_array($candidate,$reserved,true)\n            || \\App\\Models\\Post::where('slug',$candidate)->exists()\n        ) {",1);save('app/Http/Controllers/Admin/AdminPageController.php',s)
(o/'INSTALL.txt').write_text('\n\n'.join(n+'\nC:\\xampp\\htdocs\\ArizonaOutfits\\'+p.replace('/','\\') for n,p in files)+'\n\nCopy all four files, then run C:\\xampp\\php\\php.exe artisan optimize:clear. Every CMS public URL is /slug. Old /page/slug links permanently redirect for published pages. Blog articles keep root URLs. Other existing routes keep priority. New CMS slugs conflicting with existing routes or blog posts receive numeric suffixes. Privacy and Terms use their published CMS pages. Ensure existing CMS page slugs do not duplicate other existing routes or blog slugs.',encoding='utf-8')

