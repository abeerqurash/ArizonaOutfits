@extends('layouts.app')
@section('title',$page->meta_title?:$page->title)
@section('meta_description',$page->meta_description?:$page->excerpt)
@section('content')
<section class="custom-cms-page"><div class="custom-page-container"><span>Arizona Outfits</span><h1>{{ $page->title }}</h1>@if($page->excerpt)<p>{{ $page->excerpt }}</p>@endif<div class="custom-page-card"><h2>Your custom Blade content starts here</h2><p>Edit this file manually. Dashboard publishing, URL and SEO settings will continue working.</p></div></div></section>
@endsection
@push('page-styles')<style>.custom-cms-page{min-height:70vh;padding:170px 20px 90px;background:#f8fafc;color:#172033}.custom-page-container{width:min(1100px,100%);margin:auto}.custom-page-container>span{color:#0f766e;font-weight:800;text-transform:uppercase}.custom-page-container>h1{margin:10px 0;font-size:clamp(40px,8vw,82px)}.custom-page-container>p{max-width:700px;color:#64748b;font-size:18px;line-height:1.7}.custom-page-card{margin-top:45px;padding:28px;border-radius:18px;background:#fff;box-shadow:0 20px 60px rgba(15,23,42,.08)}</style>@endpush
