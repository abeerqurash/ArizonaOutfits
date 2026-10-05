@extends('layouts.app')

@section('title', 'Blogs') {{-- or $title if passed from controller --}}
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
            <div class="background-hero">
                <div class="background-overlay"></div>
            </div>
        </div>
        <div class="content-wrapper">
            <div class="content-1 content">
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">About Us</p>
            </div>
            <div class="content-2 content">
                <a href="#brands" class="moving-circle">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Lorem ipsum <br> dolor sit.</h1>
                <a href="tel:+923123743890" class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
                </a>
            </div>
            <div class="content-4 content">

            </div>
            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">Contact</div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>


        </div>
    </div>
    <div class="blog-posts">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        @foreach($posts as $post)
                        @include('partials.post-card', ['post' => $post])
                        @endforeach
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                        <div class="card-parent">
                            <div class="card-image">
                                <div class="background-image">
                                    <div class="image-overlay"></div>
                                    <div class="post-link">
                                        <a href="#brands" class="moving-circle">
                                            Read
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-information">
                                <a href="" class="post-card-description">
                                    <div class="card-heading-description">
                                        <h3 class="heading fs-18 text-color-dark">Lorem ipsum dolor sit amet consectetur.</h3>
                                        <p class="experts fs-16 text-color-body">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Esse.</p>
                                    </div>
                                    <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">Lorem, ipsum.</div>
                                    <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">02.16.26</div>
                                </a>
                                <div class="post-card-circle"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Pagination --}}
{{ $posts->links() }}
@endsection