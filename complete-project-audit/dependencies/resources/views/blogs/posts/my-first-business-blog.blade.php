@extends('layouts.app')

@section('title', 'My First Blog') {{-- or $title if passed from controller --}}
@section('meta_description', 'Welcome to our homepage')
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
            <div class="background-hero" style="background-image: url('{{ asset('storage/'.$post->feature_image) }}');">
                <div class="background-overlay"></div>
            </div>
        </div>
        <div class="content-wrapper">
            <div class="content-1 content">
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Categories:
                    @foreach($post->categories as $category)
                    <a href="{{ route('category-show', $category->slug) }}" class="fs-12 text-color-white text-uppercase letter-space-4px text-decoration-none">{{ $category->title }}</a>
                    @endforeach
                </p>
            </div>
            <div class="content-2 content">
                <a href="#brands" class="moving-circle">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
            <div class="content-3 content">
                <ul class="bread-crumbs list-style-none d-flex gap-10px align-items-center justify-content-start">

                    <li>
                        <a href="{{ route('home-page') }}"
                            class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                            Home
                        </a>
                    </li>
                    <li class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                        >
                    </li>
                    <li>
                        <a href="{{ route('blogs-page') }}"
                            class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                            Blogs
                        </a>
                    </li>
                    <li class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                        >
                    </li>
                    @if($post->categories->count())

                    @php
                    $category = $post->categories->first();
                    @endphp

                    @if($category->parent)
                    <li>
                        <a href="{{ url('/category/' . $category->parent->slug) }}"
                            class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                            {{ $category->parent->title }}
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="{{ url('/category/' . $category->slug) }}"
                            class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                            {{ $category->title }}
                        </a>
                    </li>

                    @endif
                    <li class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none cursor-pointer">
                        >
                    </li>
                    <li class="text-color-white fs-12 text-uppercase letter-space-4px text-decoration-none">
                        {{ $post->title }}
                    </li>

                </ul>
                <h1 class="fs-78 text-color-white">{{ $post->title }}</h1>
                <p class="fs-12 text-color-white text-uppercase letter-space-4px text-decoration-none">{{ $post->meta_description }}</p>
                <a href="tel:923123743890" class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
                </a>
            </div>
            <div class="content-4 content">

            </div>
            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">
                        <p>Published: {{ $post->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>


        </div>
    </div>
    <div class="scrolling-text-section no-vertical-padding">
        <div class="wrapper">
            <div class="text-info-page">
                <div class="page-info">
                    <div class="banner-item">
                        <div class="scrolling-item">
                            <div class="scrolling-text">
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div>
                                <div class="dark-dot"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="blog-posts service-content">
        <div class="wrapper">
            <div class="post-and-categories blog-single-post">
                <div class="post-cards-parent background-color-pinstrip border-top-dark">
                    <div class="parent-wrapper">
                        <div class="content">
                            <h2 class="fs-32 text-color-dark">Lorem, ipsum dolor sit amet consectetur adipisicing.</h2>
                            <p class="fs-18-400 text-color-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Vitae quae corporis aut eligendi distinctio maiores, atque modi id veniam aperiam.</p>
                            <h3 class="fs-24 text-color-dark">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Provident, molestias.</h3>
                            <div class="special-para-text fs-18-400 text-color-body background-color-white">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio fugit eos voluptatum ex minus ipsum nulla aliquam dolorum iusto quisquam.
                            </div>
                            <p class="fs-18-400 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. At, maxime error. Iure quia autem libero! Accusamus esse est dignissimos saepe?</p>
                            <p class="fs-18-400 text-color-body">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Quidem in eos architecto qui neque vero iusto delectus illum rerum ad esse voluptatem aperiam harum, quaerat maiores accusantium voluptas dolore sapiente tempora dolorum aliquam incidunt consequatur, voluptatibus alias. Necessitatibus, ullam modi.
                            </p>
                            <h2 class="fs-32 text-color-dark">Lorem ipsum dolor sit amet consectetur adipisicing.</h2>
                            <p class="fs-18-400 text-color-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi eveniet soluta cumque quisquam. Nemo iusto, harum accusantium voluptatibus accusamus incidunt.</p>
                            <h3 class="fs-24 text-color-dark">Lorem ipsum dolor sit amet consectetur adipisicing.</h3>
                            <div class="special-para-text fs-18-400 text-color-body background-color-white">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio fugit eos voluptatum ex minus ipsum nulla aliquam dolorum iusto quisquam.
                            </div>
                            <p class="fs-18-400 text-color-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi eveniet soluta cumque quisquam. Nemo iusto, harum accusantium voluptatibus accusamus incidunt.</p>
                        </div>
                    </div>
                </div>
                <div class="post-categories">
                    <div class="categories-and-title">
                        @include('blogs.partials.blog-author-card-info')
                       @include('blogs.partials.related-posts')

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="testiomonials">
        <div class="wrapper">
            <div class="testimonial-wrapper">
                <div class="testimonial-slider">
                    <div class="slider-mask">
                        <div class="testimonial-slide">
                            <div class="testimonial-slide-content">
                                <div class="testimonial-column testimonial-image">
                                    <div class="testimonial-background-image">
                                        <div class="image-overlay"></div>
                                    </div>
                                </div>
                                <div class="testimonial-column testimonial-name">
                                    <div class="name">
                                        <div class="first-name fs-48 text-capitalize text-color-white">Olivia</div>
                                        <div class="last-name fs-48 text-capitalize text-color-white">Smith</div>
                                    </div>
                                    <div class="subtitle">
                                        <div class="location-title fs-12 letter-space-4px text-color-white text-uppercase">United States</div>
                                    </div>
                                </div>
                                <div class="testimonial-column testimonial-description">
                                    <div class="description-wrapper">
                                        <div class="description text-none fs-16 text-color-white">IdeoStream has become my daily morning read. The variety of topics covered is honestly impressive — from science to culture to personal development. It never feels like clickbait, just real, thoughtful writing.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-arrow arrow-left">
                        <div class="previous">Previous</div>
                    </div>
                    <div class="testimonial-arrow arrow-right">
                        <div class="next">Next</div>
                    </div>
                </div>
            </div>
        </div>
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
    </div>
    <div class="news-letter">
        @include('blogs.partials.blog-form-container')
       
    </div>
    <div class="blog-posts service-content">
        <div class="wrapper">
            <div class="heading-grid">
                <div class="intro">
                    <div class="subtitle">
                        <span class="fs-12 letter-space-4px text-uppercase text-color-dark">Related Posts</span>
                    </div>
                    <div class="title">
                        <h1 class="fs-48 text-color-dark">Keep Learning</h1>
                    </div>
                </div>
                <div class="button">
                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                        <div class="button-text text-uppercase letter-space-3px">View All Posts</div>
                    </a>
                </div>
            </div>
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        @foreach($relatedPosts as $post)
                        @include('partials.post-card', ['post' => $post])
                        @endforeach
                    </div>
                </div>
                <div class="post-categories">
                    <div class="categories-and-title">
                        <div class="list-heading">
                            <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-dark">Popular Categories</div>
                        </div>
                        <div class="categories-list">
                            <div class="list-wrapper">

                                @foreach($popularCategories->take(10) as $category)

                                <div class="list-item">
                                    <a href="{{ url('/category/' . $category->slug) }}"
                                        class="menu-item fs-18-400 text-color-body">

                                        <div class="list-item-text">
                                            {{ $category->title }}
                                        </div>

                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>

                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection