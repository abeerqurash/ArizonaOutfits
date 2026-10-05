from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');s=(r/'resources/views/pages/custom/privacy-policy.blade.php').read_text();data='''
@php
    $posts = \\App\\Models\\Post::published()->with(['categories','primaryCategory','author'])->latestPosts()->take(6)->get();
    $latestPosts = $posts;
    $popularCategories = \\App\\Models\\Category::withCount(['posts as published_posts_count'=>fn($query)=>$query->published()])->whereHas('posts',fn($query)=>$query->published())->orderByDesc('published_posts_count')->orderBy('title')->take(10)->get();
@endphp
''';s=s.replace("@section('content')","@section('content')\n"+data,1);o=r/'custom-privacy-data-fix';o.mkdir(exist_ok=True);(o/'01_privacy-policy.blade.php.txt').write_text(s,encoding='utf-8');(o/'privacy-policy.blade.php').write_text(s,encoding='utf-8')
