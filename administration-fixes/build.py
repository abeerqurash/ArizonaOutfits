from pathlib import Path
import re,json,hashlib,html
ROOT=Path(__file__).resolve().parent.parent
OUT=ROOT/'administration-fixes'; STAGE=OUT/'_staged'; files={}
def read(p): return (ROOT/p).read_text(encoding='utf-8-sig')
def put(p,s):
    files[p]=s
    q=STAGE/p;q.parent.mkdir(parents=True,exist_ok=True);q.write_text(s,encoding='utf-8',newline='\n')
def change(p,old,new):
    s=files.get(p,read(p));assert old in s,(p,old[:100]);put(p,s.replace(old,new))
def method(p,name,body):
    s=files.get(p,read(p));m=re.search(r'(?:public|private|protected)\s+(?:static\s+)?function\s+'+name+r'\s*\(',s);assert m,(p,name)
    # Existing methods have balanced literal braces; tokenize quotes/comments too.
    start=m.start();brace=s.index('{',m.end());i=brace+1;depth=1;quote=None
    while depth:
        c=s[i]
        if quote:
            if c=='\\':i+=2;continue
            if c==quote:quote=None
        elif c in "\"'":quote=c
        elif s[i:i+2]=='/*':i=s.index('*/',i+2)+2;continue
        elif s[i:i+2]=='//':i=s.index('\n',i)+1;continue
        elif c=='{':depth+=1
        elif c=='}':depth-=1
        i+=1
    put(p,s[:start]+body.strip()+s[i:])

put('app/Services/SafeContentUrl.php',r'''<?php
namespace App\Services;
class SafeContentUrl
{
    public static function allowed(string $url, bool $mailLinks = false): bool
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) return false;
        if (str_starts_with($url, '//')) return false;
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) return true;
        $parts = parse_url($url);
        if (!$parts || isset($parts['user']) || isset($parts['pass'])) return false;
        $scheme = strtolower($parts['scheme'] ?? '');
        if ($mailLinks && in_array($scheme, ['mailto','tel'], true)) return strlen($url) > strlen($scheme)+1;
        return in_array($scheme, ['http','https'], true) && (bool) filter_var($url, FILTER_VALIDATE_URL);
    }
    public static function internal(string $url): bool
    {
        if (!self::allowed($url)) return false;
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) return true;
        $parts=parse_url($url);
        return strtolower($parts['scheme']) === request()->getScheme()
            && strcasecmp($parts['host'],request()->getHost()) === 0
            && ($parts['port'] ?? ($parts['scheme']==='https'?443:80)) === request()->getPort();
    }
}
''')
put('app/Services/HtmlContentSanitizer.php',r'''<?php
namespace App\Services;
use DOMDocument;
use DOMElement;
use DOMNode;
class HtmlContentSanitizer
{
    public function clean(?string $html): string
    {
        if (trim((string)$html)==='') return '';
        $doc=new DOMDocument('1.0','UTF-8');$old=libxml_use_internal_errors(true);
        try { $doc->loadHTML('<?xml encoding="UTF-8"><html><body>'.(string)$html.'</body></html>', LIBXML_NONET); }
        finally {libxml_clear_errors();libxml_use_internal_errors($old);}
        $body=$doc->getElementsByTagName('body')->item(0);if(!$body)return '';
        $this->filter($body);$result='';foreach($body->childNodes as $child)$result.=$doc->saveHTML($child);
        return trim($result);
    }
    private function filter(DOMNode $parent): void
    {
        $tags=['h1','h2','h3','h4','h5','h6','p','br','strong','b','em','i','u','s','ul','ol','li','a','img','table','thead','tbody','tfoot','tr','th','td','hr','blockquote','code','pre','span','div'];
        foreach(iterator_to_array($parent->childNodes) as $node){
            if($node->nodeType===XML_COMMENT_NODE){$parent->removeChild($node);continue;}
            if(!$node instanceof DOMElement)continue;
            $tag=strtolower($node->tagName);
            if(in_array($tag,['script','style','iframe','object','embed','svg','math','template','form','input','button','textarea','select','link','meta','base'],true)){$parent->removeChild($node);continue;}
            $this->filter($node);
            if(!in_array($tag,$tags,true)){while($node->firstChild)$parent->insertBefore($node->firstChild,$node);$parent->removeChild($node);continue;}
            foreach(iterator_to_array($node->attributes) as $attr){
                $name=strtolower($attr->name);$value=$attr->value;
                $allowed=in_array($name,['title','alt'],true)
                    || ($tag==='a' && $name==='href' && SafeContentUrl::allowed($value,true))
                    || ($tag==='img' && $name==='src' && SafeContentUrl::allowed($value))
                    || (in_array($tag,['td','th'],true) && in_array($name,['colspan','rowspan'],true) && ctype_digit($value) && (int)$value<=100)
                    || (in_array($tag,['table','td','th','img'],true) && in_array($name,['width','height'],true) && preg_match('/^\d{1,4}%?$/',$value));
                if(!$allowed)$node->removeAttributeNode($attr);
            }
        }
    }
}
''')
put('app/Services/CmsContentSanitizer.php','<?php\nnamespace App\\Services;\nclass CmsContentSanitizer { public function clean(?string $html): string { return app(HtmlContentSanitizer::class)->clean($html); } }\n')
p='app/Services/EmailTemplateService.php'
method(p,'sanitizeHtml',r'''public function sanitizeHtml(string $html): string { return app(HtmlContentSanitizer::class)->clean($html); }''')
method(p,'replace',r'''public function replace(string $content, array $variables, bool $escapeValues = true): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',function($m)use($variables,$escapeValues){
            $value=(string)($variables[$m[1]]??'');return $escapeValues?e($value):strip_tags($value);
        },$content)??'';
    }''')
change(p,'private function viewForSlug','public function viewForSlug')
p='app/Http/Middleware/AdminMiddleware.php'
change(p,"default => null,", """$request->routeIs('admin.settings.*', 'admin.email-templates.*') => 'settings.manage',
                $request->routeIs('admin.pages.*', 'admin.navigation-menus.*') => 'content.manage',
                $request->routeIs('admin.analytics.*') => 'dashboard.view',
                $request->routeIs('admin.admin-users.*', 'admin.admin-roles.*') => 'admin-users.manage',
                $request->routeIs('admin.notifications.*') => 'notifications.manage',
                $request->routeIs('admin.audit-logs.*') => 'audit-logs.view',
                $request->routeIs('admin.backups.*') => 'backups.manage',
                default => null,""")
change('resources/views/admin/partials/administration-links.blade.php',"auth()->user()","auth('admin')->user()")
p='resources/views/admin/partials/sidebar.blade.php';s=read(p);loc=s.index("href=\"{{ route('admin.settings.edit') }}\"");a=s.rfind('<a',0,loc);b=s.index('</a>',loc)+4;put(p,s[:a]+"@if(auth('admin')->user()?->hasAdminPermission('settings.manage'))\n"+s[a:b]+"\n@endif"+s[b:])
p='routes/web.php';s=read(p);assert r'[a-z0-9]+(?:-[a-z0-9]+)\\*' in s;put(p,s.replace(r'[a-z0-9]+(?:-[a-z0-9]+)\\*','[a-z0-9]+(?:-[a-z0-9]+)*'))
p='app/Http/Controllers/Admin/AdminPageController.php'
for old in ['$page = Page::create($data);','$page->update($data);','$page->delete();']:
    change(p,old,old+'\n        app(\\App\\Services\\NavigationMenuService::class)->forget();')
p='app/Models/NavigationMenuItem.php'
method(p,'getResolvedUrlAttribute',r'''public function getResolvedUrlAttribute(): string
    {
        if($this->link_type==='page')return $this->page?->status==='published' && (!$this->page->published_at || $this->page->published_at->lte(now()))?$this->page->public_url:'#';
        if($this->link_type==='route' && $this->route_name && Route::has($this->route_name))return route($this->route_name);
        $url=trim((string)$this->url);return \App\Services\SafeContentUrl::allowed($url)?(str_starts_with($url,'/')?url($url):$url):'#';
    }''')
p='app/Http/Controllers/Admin/AdminNavigationMenuController.php'
method(p,'reorder',r'''public function reorder(Request $request,NavigationMenu $menu,NavigationMenuService $service): RedirectResponse
    {
        $data=$request->validate(['items'=>['required','array','max:100'],'items.*'=>['integer','distinct',\Illuminate\Validation\Rule::exists('navigation_menu_items','id')->where('navigation_menu_id',$menu->id)]]);
        \Illuminate\Support\Facades\DB::transaction(function()use($menu,$data){
            NavigationMenu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            $allowed=$menu->items()->lockForUpdate()->pluck('id')->map(fn($id)=>(int)$id)->sort()->values()->all();
            $requested=collect($data['items'])->map(fn($id)=>(int)$id)->sort()->values()->all();
            if($requested!==$allowed)throw \Illuminate\Validation\ValidationException::withMessages(['items'=>'Refresh the page and reorder all current menu items.']);
            foreach(array_values($data['items'])as $position=>$id)$menu->items()->whereKey($id)->update(['position'=>$position]);
        });$service->forget();return back()->with('success','Menu order saved.');
    }''')
method(p,'validated',r'''private function validated(Request $request): array
    {
        return $request->validate(['label'=>['required','string','max:100'],'link_type'=>['required','in:page,route,custom'],'page_id'=>['nullable','required_if:link_type,page','exists:pages,id'],'route_name'=>['nullable','required_if:link_type,route','string',Rule::in(['home-page','about-page','blogs-page','products.index','cart.index','contact-page','customer.dashboard'])],'url'=>['nullable','required_if:link_type,custom','string','max:1000',function($a,$v,$fail){if($v&&!\App\Services\SafeContentUrl::allowed($v))$fail('Enter a safe internal path or complete HTTP/HTTPS URL.');}],'is_active'=>['nullable','boolean'],'open_in_new_tab'=>['nullable','boolean']]);
    }''')
p='app/Http/Controllers/Admin/AdminNotificationController.php'
method(p,'safeInternalUrl',r'''private function safeInternalUrl(string $url): bool { return \App\Services\SafeContentUrl::internal($url); }''')
change(p,"'user_ids.*' => [\n                'integer',","'user_ids.*' => [\n                'integer', 'distinct',")
p='app/Http/Controllers/Admin/AdminEmailTemplateController.php'
change(p,r'/\{\{([a-zA-Z0-9_]+)\}\}/',r'/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/')
change(p,"view('emails.dynamic-template',", "view($service->viewForSlug($emailTemplate->slug),")
change(p,"'emails.dynamic-template',\n            [","$service->viewForSlug($emailTemplate->slug),\n            [")
put('resources/views/admin/email-templates/edit.blade.php',r'''@extends('admin.layouts.app')
@section('title','Edit Email Template')
@section('page-heading','Edit Email Template')
@section('content')
<div style="max-width:1000px;margin:auto;background:white;padding:24px;border:1px solid #e2e8f0;border-radius:16px">
<a href="{{ route('admin.email-templates.index') }}">Back to email templates</a>
<h2>{{ $emailTemplate->name }}</h2>
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
@if($errors->any())<ul role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" action="{{ route('admin.email-templates.update',$emailTemplate) }}">@csrf @method('PUT')
<label for="subject">Subject</label><input id="subject" name="subject" maxlength="255" required value="{{ old('subject',$emailTemplate->subject) }}" style="display:block;width:100%;padding:12px;margin:8px 0 20px;box-sizing:border-box">
<label for="body">Email body (HTML)</label><textarea id="body" name="body" rows="20" required maxlength="50000" style="display:block;width:100%;padding:12px;box-sizing:border-box;font-family:monospace">{{ old('body',$emailTemplate->body) }}</textarea>
<p>Available variables: @foreach($emailTemplate->available_variables ?? [] as $variable)<code>{{ '{'.'{'.$variable.'}'.'}' }}</code> @endforeach</p>
<p>Basic HTML is supported. Unsafe tags and attributes are removed when rendering.</p>
<input type="hidden" name="is_enabled" value="0"><label><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled',$emailTemplate->is_enabled))> Use this custom template (otherwise use the built-in email)</label>
<p><button type="submit">Save template</button> <a href="{{ route('admin.email-templates.preview',$emailTemplate) }}" target="_blank" rel="noopener">Preview saved template</a></p>
</form>
<hr><h3>Send a test email</h3><form method="POST" action="{{ route('admin.email-templates.test',$emailTemplate) }}">@csrf
<label for="test-email">Recipient email</label><input type="email" name="email" id="test-email" required maxlength="255"><button type="submit">Send test email</button>
</form></div>
@endsection
''')
p='app/Http/Middleware/AdminAuditMiddleware.php'
change(p,"$response->getStatusCode() >= 400 ? 'failed' : 'success',", "($response->getStatusCode() >= 400 || ($request->hasSession() && array_intersect(['error','errors'], $request->session()->get('_flash.new', [])))) ? 'failed' : 'success',")
change(p,"                500,\n                'failed',", "                $exception instanceof \\Illuminate\\Validation\\ValidationException ? 422 : ($exception instanceof \\Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface ? $exception->getStatusCode() : ($exception instanceof \\Illuminate\\Auth\\Access\\AuthorizationException ? 403 : ($exception instanceof \\Illuminate\\Auth\\AuthenticationException ? 401 : 500))),\n                'failed',")

# Additional coordinated patches are appended by integration.py before export.
if (OUT/'integration.py').exists(): exec((OUT/'integration.py').read_text(encoding='utf-8'))
manifest=[]
for old in OUT.glob('[0-9][0-9]_*.txt'): old.unlink()
for n,(p,s) in enumerate(files.items(),1):
    name=f'{n:02d}_{Path(p).name}.txt';(OUT/name).write_text(s,encoding='utf-8',newline='\n')
    manifest.append({'file':name,'destination':p,'new':not(ROOT/p).exists(),'sha256':hashlib.sha256(s.encode()).hexdigest(),'original_sha256':hashlib.sha256((ROOT/p).read_bytes()).hexdigest() if (ROOT/p).exists() else None})
(OUT/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
(OUT/'FILES.html').write_text('<!doctype html><meta charset="utf-8"><title>Administration replacement files</title><h1>Separate replacement TXT files</h1><p>Read INSTALL.txt first. Replace the complete contents of each destination file.</p><ol>'+''.join(f'<li><a href="{x["file"]}">{x["file"]}</a><br><code>{html.escape(str(ROOT/x["destination"]))}</code>'+(' — NEW FILE' if x['new'] else '')+'</li>' for x in manifest)+'</ol>',encoding='utf-8')
print(f'Prepared {len(manifest)} separate files. Installed project unchanged.')
