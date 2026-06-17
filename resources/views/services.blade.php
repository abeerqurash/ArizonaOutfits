@extends('layouts.app')

@section('title', 'Services') {{-- or $title if passed from controller --}}
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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Services</p>
            </div>
            <div class="content-2 content">
                <a href="#brands" class="moving-circle">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Where Ideas <br> Meet Action</h1>
                <a href="tel:923123743890" class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
                </a>
            </div>
            <div class="content-4 content">

            </div>
            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">Ideas Shaped For You</div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>


        </div>
    </div>
    <div class="recent-projects">
        <div class="wrapper">
            <div class="project-collection">
                <div class="project-list">
                    <div class="list-item item-1">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-1">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Your Vision Goes Live</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Web Development</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="/services/web-development" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-2">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-2">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Simple Sites, Big Impact</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Wordpress Development</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="/services/wordpress-development" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-3">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-3">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Apps Humans Love Using</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Laravel Development</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="/services/laravel-development" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-1">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-1">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Visibility You Truly Deserve</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Technical SEO</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="/services/technical-seo" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-2">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-2">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Free Your Time Today</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Automation</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="/services/wordpress-development" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-3">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-3">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Bridges Between Your Systems</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">API Integiration</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="/services/laravel-development" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection