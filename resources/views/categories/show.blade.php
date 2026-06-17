@extends('layouts.app')

@section('title', $category->meta_title ?? $category->title)
@section('meta_description', $category->meta_description)

@section('content')

<h1>{{ $category->title }}</h1>

<div class="blog-grid">
    @foreach($posts as $post)
        @include('partials.post-card', ['post' => $post])
    @endforeach
</div>

{{ $posts->links() }}

@endsection