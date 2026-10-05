<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$files=['resources/views/admin/partials/sidebar.blade.php','resources/views/admin/partials/administration-links.blade.php'];
$routes=$app['router']->getRoutes();$results=[];
foreach($files as $file){$source=file_get_contents(__DIR__.'/../'.$file);preg_match_all('/<a\b[^>]*href="\{\{\s*route\(\s*\x27([^\x27]+)\x27\s*\).*?<\/a>/s',$source,$anchors,PREG_SET_ORDER);
foreach($anchors as $anchor){$routeName=$anchor[1];preg_match('/<span\s+class="admin-menu-text"\s*>(.*?)<\/span>/s',$anchor[0],$labelMatch);$label=isset($labelMatch[1])?trim(strip_tags($labelMatch[1])):'Brand / Dashboard';$label=preg_replace('/\s+/',' ',$label);
$row=['label'=>$label,'route'=>$routeName,'source'=>$file,'exists'=>false];$route=$routes->getByName($routeName);
if($route){$row['exists']=true;$row['uri']=$route->uri();$row['methods']=$route->methods();$row['url']=route($routeName);$row['action']=$route->getActionName();$row['middleware']=$route->gatherMiddleware();
$matched=$routes->match(Illuminate\Http\Request::create('http://localhost/'.$route->uri(),'GET'));$row['matched_route']=$matched->getName();
if(str_contains($row['action'],'@')){[$class,$method]=explode('@',$row['action']);$row['controller_exists']=class_exists($class)&&method_exists($class,$method);if($row['controller_exists']){$reflection=new ReflectionMethod($class,$method);$lines=file($reflection->getFileName());$body=implode('',array_slice($lines,$reflection->getStartLine()-1,$reflection->getEndLine()-$reflection->getStartLine()+1));$row['controller_line']=$reflection->getStartLine();$row['controller_file']=$reflection->getFileName();preg_match_all('/\bview\(\s*\x27([^\x27]+)\x27/s',$body,$views);$row['views']=array_map(fn($view)=>['name'=>$view,'exists'=>$app['view']->exists($view)],array_unique($views[1]));$row['explicit_permission_check']=str_contains($body,'hasAdminPermission')||str_contains($body,'authorize(');}}
preg_match('/request\(\)->routeIs\((.*?)\)/s',$anchor[0],$active);preg_match_all('/\x27([^\x27]+)\x27/',$active[1]??'',$patterns);$row['active_patterns']=$patterns[1];
}
$results[]=$row;}}
$logout=$routes->getByName('admin.logout');$logoutResult=['name'=>'admin.logout','exists'=>(bool)$logout,'methods'=>$logout?->methods(),'action'=>$logout?->getActionName()];
$missing=[];foreach($results as $r){if(!$r['exists']||($r['matched_route']??null)!==$r['route']||($r['controller_exists']??false)!==true)$missing[]=$r['label'];foreach($r['views']??[] as $view)if(!$view['exists'])$missing[]=$r['label'].' view '.$view['name'];}
$overlaps=[];foreach($results as $r){if(str_starts_with($r['route'],'admin.')){$activeLabels=[];foreach($results as $menu){foreach($menu['active_patterns']??[] as $pattern){if(Illuminate\Support\Str::is($pattern,$r['route'])){$activeLabels[]=$menu['label'];break;}}}if(count($activeLabels)>1)$overlaps[$r['route']]=$activeLabels;}}
$allAdminIndexes=[];foreach($routes as $route){$name=$route->getName();if(str_starts_with($name??'','admin.') && str_ends_with($name,'.index') && !in_array($name,array_column($results,'route')))$allAdminIndexes[]=$name;}
$report=['menu_entries'=>count($results),'unique_destinations'=>count(array_unique(array_column($results,'route'))),'results'=>$results,'logout'=>$logoutResult,'broken_targets'=>$missing,'active_overlaps'=>$overlaps,'index_routes_without_direct_sidebar_entry'=>$allAdminIndexes];
file_put_contents(__DIR__.'/menu-audit.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode(['entries'=>$report['menu_entries'],'unique_destinations'=>$report['unique_destinations'],'broken_targets'=>$missing,'active_overlaps'=>$overlaps,'unlinked_indexes'=>$allAdminIndexes,'logout'=>$logoutResult],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
foreach($results as $r)echo $r['label'].' | '.$r['route'].' | '.implode(',',array_column($r['views']??[],'name'))."\n";
