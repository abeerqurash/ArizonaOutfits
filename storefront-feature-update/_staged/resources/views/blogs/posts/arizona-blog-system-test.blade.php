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

    

    

@include('blogs.partials.related-article-cards')
@include('blogs.partials.article-reviews', ['reviewProducts'=>$reviewProducts])
</div>

@endsection
