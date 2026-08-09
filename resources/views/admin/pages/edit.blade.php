@extends('admin.layouts.app')
@section('title','Edit '.$page->title)
@section('page-heading','Edit CMS Page')
@section('content')<div class="page-editor"><header class="editor-heading"><a href="{{ route('admin.pages.index') }}"><i class="fa-solid fa-arrow-left"></i> CMS Pages</a><h2>{{ $page->title }}</h2><p>Last updated {{ $page->updated_at?->diffForHumans() }}.</p></header>@include('admin.pages._form')</div>@endsection
