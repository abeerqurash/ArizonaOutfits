from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
css=(r/'complete-project-audit/critical-home.css').read_text(encoding='utf-8');css=css.replace('url(/asset/media/hero.webp)',r"url('{{ app(\App\Services\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),1280) }}')")
save('resources/views/partials/home-critical-styles.blade.php','<style>'+css+'</style>\n')
p='resources/views/layouts/app.blade.php';s=(o/'_staged'/p).read_text(encoding='utf-8')
a=re.search(r'    <link\s+rel="stylesheet"\s+href="\{\{ asset\(\'asset/css/style.css\'\) \}\}"\s*>',s);assert a
replacement='''    @if(request()->routeIs('home-page'))
        @include('partials.home-critical-styles')
        <link rel="stylesheet" href="{{ asset('asset/css/style.css') }}" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="{{ asset('asset/css/style.css') }}"></noscript>
    @else
        <link rel="stylesheet" href="{{ asset('asset/css/style.css') }}">
    @endif'''
s=s[:a.start()]+replacement+s[a.end():]
s=re.sub(r'(<link\s+rel="stylesheet"\s+href="https://(?:cdnjs.cloudflare.com|cdn.jsdelivr.net)[^"]*")',r'\1 media="print" onload="this.media=\'all\'"',s)
# Noscript users retain external icon and phone styles too.
s=s.replace("    @stack('page-styles')",'''    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css"></noscript>
    @stack('page-styles')''')
s=s.replace("  $private=", "  $private=")
save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
