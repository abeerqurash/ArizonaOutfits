@extends('layouts.app')



@section('title', 'Arizona Blog System Test') {{-- or $title if passed from controller --}}

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

    @include('blogs.partials.article-hero', ['post' => $post])

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

                @include('blogs.partials.article-author-latest', [

    'post' => $post,

    'latestPosts' => $latestPosts

])

            </div>

        </div>

    </div>

    @include('blogs.partials.article-reviews', [

    'reviewProducts' => $reviewProducts

])

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

                        <h2 class="fs-48 text-color-dark">Keep Learning</h2>

                    </div>

                </div>

                <div class="button">

                    <a href="{{ route('blogs-page') }}" class="btn-style-2 fs-12 text-color-white justify-self-start">

                        <div class="button-text text-uppercase letter-space-3px">View All Posts</div>

                    </a>

                </div>

            </div>

            <div class="post-and-categories">

                <div class="post-cards-parent">

                    <div class="parent-wrapper az-responsive-cards" data-card-carousel aria-label="Related articles">
@foreach($relatedPosts as $relatedPost)

                        @include('partials.post-card', ['post' => $relatedPost])

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

                                @forelse($popularCategories as $popularCategory)
                                    <div class="list-item">
                                        <a href="{{ route('category-show', $popularCategory->slug) }}" class="menu-item fs-18-400 text-color-body">
                                            <div class="list-item-text">{{ $popularCategory->title }}</div>
                                            <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                        </a>
                                    </div>
                                @empty
                                    <div class="list-item">
                                        <span class="menu-item fs-18-400 text-color-body">
                                            <div class="list-item-text">No categories available yet.</div>
                                        </span>
                                    </div>
                                @endforelse

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
