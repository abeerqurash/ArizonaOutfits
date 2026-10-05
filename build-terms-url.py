from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'terms-clean-url-files';o.mkdir(exist_ok=True)
files=[]
def save(p,s):
 n=f'{len(files)+1:02d}_'+Path(p).name+'.txt';(o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8');files.append((n,p))
s=(r/'routes/web.php').read_text();s=s.replace("[TermsAndCondtionsController::class, 'index']", "fn () => app(CmsPageController::class)->show('terms-and-conditions')",1);save('routes/web.php',s)
s=(r/'app/Models/Page.php').read_text().replace("        return route('pages.show', $this->slug);","        return $this->slug === 'terms-and-conditions'\n            ? route('terms-and-conditions-page')\n            : route('pages.show', $this->slug);");save('app/Models/Page.php',s)
s=(r/'app/Http/Controllers/CmsPageController.php').read_text().replace('public function show(string $slug): View','public function show(string $slug): View|\\Illuminate\\Http\\RedirectResponse').replace("        $page=Page::published()", "        if($slug==='terms-and-conditions' && request()->routeIs('pages.show'))return redirect()->route('terms-and-conditions-page',[],301);\n        $page=Page::published()",1);save('app/Http/Controllers/CmsPageController.php',s)
(o/'INSTALL.txt').write_text('\n\n'.join(n+'\nC:\\xampp\\htdocs\\ArizonaOutfits\\'+p.replace('/','\\') for n,p in files)+'\n\nRun C:\\xampp\\php\\php.exe artisan optimize:clear. Published CMS Terms page now opens at /terms-and-conditions. The old /page/terms-and-conditions redirects permanently. Other CMS page URLs remain unchanged. Ensure the CMS Terms page is published and its custom template exists.',encoding='utf-8')
