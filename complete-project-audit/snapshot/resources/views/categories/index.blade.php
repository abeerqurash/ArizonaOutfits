@extends('layouts.app')

@section('title', 'Blog Categories')
@section('meta_description', 'Explore Arizona Outfits blog categories and discover articles, ideas, stories, and insights across every topic.')

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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Blog Categories</p>
            </div>

            <div class="content-2 content">
                <a href="#blog-categories" class="moving-circle" aria-label="Explore blog categories">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>

            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Explore Stories <br> By Category</h1>

                <a href="{{ route('blogs-page') }}"
                   class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">View All Posts</div>
                </a>
            </div>

            <div class="content-4 content"></div>

            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                        Discover Every Perspective
                    </div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="blog-posts" id="blog-categories">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        @forelse($categories as $category)
                            @include('partials.post-card', ['category' => $category])
                        @empty
                            <div class="text-color-body">
                                No blog categories are available yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
