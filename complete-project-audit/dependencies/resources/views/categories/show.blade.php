@extends('layouts.app')

@section('title', $category->meta_title ?? $category->title)
@section('meta_description', $category->meta_description ?? ('Explore the latest ' . $category->title . ' articles, stories, ideas, and insights from Arizona Outfits.'))

@section('content')
<div class="page-wrapper">
    <div class="strip-wrapper">
        <div class="wrapper">
            <div class="strip-container">
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
            </div>
        </div>
    </div>

    <div class="home-hero">
        <div class="stripe-wrapper">
            <div class="wrapper">
                <div class="stripe-container">
                    <div class="pin-stripe white"></div>
                    <div class="pin-stripe white"></div>
                    <div class="pin-stripe white"></div>
                    <div class="pin-stripe white"></div>
                </div>
            </div>
        </div>

        <div class="background-cover">
            <div class="background-hero">
                <div class="background-overlay"></div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="content-1 content">
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">
                    Blog Category
                </p>
            </div>

            <div class="content-2 content">
                <a href="#category-posts"
                   class="moving-circle"
                   aria-label="Explore {{ $category->title }} articles">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>

            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">
                    {{ $category->title }}
                </h1>

                <a href="{{ route('blogs-page') }}"
                   class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">
                        View All Posts
                    </div>
                </a>
            </div>

            <div class="content-4 content"></div>

            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                        Explore Articles &amp; Ideas
                    </div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="blog-posts" id="category-posts">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        @forelse($posts as $post)
                            @include('partials.post-card', ['post' => $post])
                        @empty
                            <div class="text-color-body">
                                No published articles are available in this category yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if(method_exists($posts, 'links') && $posts->hasPages())
                <div class="blog-pagination">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
