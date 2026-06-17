@extends('layouts.app')

@section('title', 'Ideostream — Every Topic the World Is Talking About') {{-- or $title if passed from controller --}}
@section('meta_description', 'From tech and health to finance, culture and beyond — Ideostream covers everything that matters. Real articles, honest insights and fresh content written for curious minds daily.')

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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Dive Deep, Think Bigger</p>
            </div>
            <div class="content-2 content">
                <a href="#services" class="moving-circle">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Explore Everything <br> Around You</h1>
                <a href="/blogs" class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">Browse the World</div>
                </a>
            </div>
            <div class="content-4 content">

            </div>
            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">Welcome</div>
                    <div class="horizontal-line white"></div>
                </div>
            </div>


        </div>
    </div>
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="service-heading-button">
                <div class="title-wrapper">
                    <div class="sub-title fs-12 letter-space-4px text-uppercase"><span>Products</span></div>
                    <div class="title">
                        <h2 class="fs-48 text-color-dark">Popular Products</h2>
                    </div>
                </div>
                <div class="view-all-services">
                    <a href="/services" class="btn-style-2 fs-12 text-color-white justify-self-start">
                        <div class="button-text text-uppercase letter-space-3px">Shop Now</div>
                    </a>
                </div>
            </div>
            <div class="services-grid">
                <div class="service-parent">
                    <div class="service-circle"></div>
                    <div class="service-card">
                        <div class="icon"><img src="{{ asset('asset/media/web-development.png') }}" alt=""></div>
                        <div class="featured-card-info">
                            <div class="card-description">
                                <h3 class="fs-24 text-color-dark">Web Development</h3>
                                <p class="text-color-body fs-16">Build stunning, high-performance websites tailored to your vision, combining clean code, bold design, and smart functionality that keeps your audience engaged and coming back.</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="/web-development" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read More</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="service-parent">
                    <div class="service-circle"></div>
                    <div class="service-card">
                        <div class="icon"><img src="{{ asset('asset/media/wordpress-development.png') }}" alt=""></div>
                        <div class="featured-card-info">
                            <div class="card-description">
                                <h3 class="fs-24 text-color-dark">Wordpress Development</h3>
                                <p class="text-color-body fs-16">Create a fully customized WordPress experience that looks incredible, loads instantly, and gives you complete control over your content, design, and business presence online.</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="/wordpress-development" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read More</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="service-parent">
                    <div class="service-circle"></div>
                    <div class="service-card">
                        <div class="icon"><img src="{{ asset('asset/media/technical-seo.png') }}" alt=""></div>
                        <div class="featured-card-info">
                            <div class="card-description">
                                <h3 class="fs-24 text-color-dark">Technical SEO</h3>
                                <p class="text-color-body fs-16">Strengthen your site's foundation with advanced technical SEO strategies that improve crawlability, speed, and structure, putting you miles ahead of every competitor online.</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="/technical-seo" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read More</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="service-parent">
                    <div class="service-circle"></div>
                    <div class="service-card">
                        <div class="icon"><img src="{{ asset('asset/media/laravel.png') }}" alt=""></div>
                        <div class="featured-card-info">
                            <div class="card-description">
                                <h3 class="fs-24 text-color-dark">Laravel</h3>
                                <p class="text-color-body fs-16">Build secure, scalable web applications with Laravel's powerful framework, engineered for performance and reliability to handle your most complex business logic with ease.</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="/laravel" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read More</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="service-parent">
                    <div class="service-circle"></div>
                    <div class="service-card">
                        <div class="icon"><img src="{{ asset('asset/media/integiration.png') }}" alt=""></div>
                        <div class="featured-card-info">
                            <div class="card-description">
                                <h3 class="fs-24 text-color-dark">Automation</h3>
                                <p class="text-color-body fs-16">Work smarter with intelligent automation that handles the heavy lifting, reduces human error, cuts operational costs, and scales effortlessly alongside your growing business needs.</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="/automation" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read More</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="service-parent">
                    <div class="service-circle"></div>
                    <div class="service-card">
                        <div class="icon"><img src="{{ asset('asset/media/api-integration.png') }}" alt=""></div>
                        <div class="featured-card-info">
                            <div class="card-description">
                                <h3 class="fs-24 text-color-dark">API Integiration</h3>
                                <p class="text-color-body fs-16">Bridge your tools and systems with seamless API integration, unlocking powerful new capabilities, eliminating data silos, and creating a fully connected workflow built for scale.</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="/api-integration" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read More</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="about">
        <div class="about-wrapper">
            <div class="dual-wrapper">
                <div class="about-info">
                    <div class="small-heading">
                        <p class="sub-title text-color-white fs-12 text-uppercase letter-space-4px">About IDEOSTREAM</p>
                    </div>
                    <div class="about-content">
                        <h1 class="fs-48 text-color-white">From breaking tech news to personal finance, health advice to cultural moments — we cover it all under one roof. No gatekeeping, no limits, just honest content built for people who are hungry to learn something new every single day.</h1>
                        <div class="content">
                            <p class="text-color-white fs-12 text-uppercase letter-space-4px">IDEOSTREAM, Founder</p>
                        </div>
                    </div>
                    <div class="video-link-wrapper">
                        <a href="/about" class="btn-style-1 fs-12 text-color-white justify-self-start">
                            <div class="button-text text-uppercase letter-space-3px">About Founder</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="background-cover">
            <div class="banner-cover">
                <div class="cover-overlay"></div>
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
        <div class="bottom-cover">
            <div class="strip-wrapper">
                <div class="wrapper">
                    <div class="strip-ctr">
                        <div class="pin-strip"></div>
                        <div class="pin-strip"></div>
                        <div class="pin-strip"></div>
                        <div class="pin-strip"></div>
                        <div class="pin-strip"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="what-i-do">
        <div class="wrapper wrapper-1">
            <div class="banner-wrapper">
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
                <div class="banner-content">
                    <div class="banner-intro">
                        <div class="title-subtitle">
                            <div class="subtitle">
                                <span class="fs-12 text-uppercase text-color-white letter-space-4px">Real work, real results.</span>
                            </div>
                            <div class="title">
                                <h1 class="fs-48 text-color-white">Work We Are Proud</h1>
                            </div>
                        </div>
                        <div class="all-projects">
                            <a href="/projects" class="btn-style-1 fs-12 text-color-white justify-self-start">
                                <div class="button-text text-uppercase letter-space-3px">View All Projects</div>
                            </a>
                        </div>
                    </div>
                    <div class="background-banner">
                        <div class="background-overlay"></div>
                    </div>
                </div>
                <div class="image-cover-wrapper">
                    <div class="image-cover"></div>
                </div>
            </div>
        </div>
        <div class="wrapper wrapper-2">
            <div class="project-categories">
                <div class="categories-wrapper">
                    <div class="categories-collection">
                        <div class="categories-list">
                            <div class="category category-item-1">
                                <div class="card">
                                    <div class="card-circle"></div>
                                    <a href="" class="card-link">
                                        <div class="title fs-12 text-color-dark letter-space-4px text-decoration-none text-uppercase">Web Development</div>
                                    </a>
                                </div>
                            </div>
                            <div class="category category-item text-uppercase">
                                <div class="card">
                                    <div class="card-circle"></div>
                                    <a href="" class="card-link">
                                        <div class="title fs-12 text-color-dark letter-space-4px text-decoration-none text-uppercase">Wordpress Development</div>
                                    </a>
                                </div>
                            </div>
                            <div class="category category-item-3">
                                <div class="card">
                                    <div class="card-circle"></div>
                                    <a href="" class="card-link">
                                        <div class="title fs-12 text-color-dark letter-space-4px text-decoration-none text-uppercase">Laravel Development</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
        <div class="gird-wrapper">
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
            <div class="newsletter-wrapper">
                <div class="news-letter-intro">
                    <div class="content-wrapper">
                        <div class="subtitle-wrapper">
                            <div class="subtitle fs-12 text-uppercase letter-space-4px text-color-dark">Get A Qoute</div>
                        </div>
                        <div class="heading-wrapper">
                            <div class="heading fs-32 text-color-dark text-none">Let's Build Something Great Together.</div>
                        </div>
                    </div>
                    <div class="checklist">
                        <div class="checklist-item item-1">
                            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="check-content text-color-body fs-16">Tell us about your dream project vision.</div>
                        </div>
                        <div class="checklist-item item-1">
                            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="check-content text-color-body fs-16">We craft solutions tailored to your needs.</div>
                        </div>
                        <div class="checklist-item item-1">
                            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="check-content text-color-body fs-16">Get your free quote within 24 hours.</div>
                        </div>
                    </div>
                </div>
                <div class="portfolio-form">
                    <form action="{{ url('/home-form-submit') }}" method="POST" class="home-form" id="home-form-submission">
    @csrf
                        <div class="form-radio-column">
                            <div class="radio-menu">
                                <div class="menu-list">
                                    <label class="menu-item menu-item-1">
                                        <div class="raido-button radio-btn-ctr"></div>
                                        <input type="radio" name="formType" class="radio-input contact-us-radio" id="contactUsRadio" value="Contact Us" required>
                                        <span class="list-item-text" for="formType_1">Contact Us</span>
                                    </label>
                                    <label class="menu-item menu-item-1">
                                        <div class="raido-button radio-btn-ctr"></div>
                                        <input type="radio" name="formType" class="radio-input prContacts-radio" id="prContacts" value="PR" required>
                                        <span class="list-item-text" for="formType_2">PR</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-column right">
                            <div class="form">
                                <div class="field-wrapper">
                                    <label for="firstName" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">Name</label>
                                    <input type="text" name="fullName" id="fullname" placeholder="Full Name" class="name-field input-field" required>
                                    <input type="text" name="subject" id="subject" placeholder="Subject" class="name-field input-field" required>
                                </div>
                                <div class="field-wrapper">
                                    <label for="emailAddress" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Email</label>
                                    <label for="phoneNumber" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Phone Number</label>
                                    <input type="email" name="emailAddress" id="emailAddress" placeholder="Your Email" class="email-field input-field" required>
                                    <input type="tel" name="phoneNumber" id="phoneNumber" placeholder="Phone Number" class="phone-field input-field" required>
                                </div>
                                <textarea name="messageBox" id="messageBox" placeholder="Message" class="textarea-field input-field" rows="8"></textarea>
                                <input type="submit" class="submit-button form-btn" value="Send Now">

                            </div>
                        </div>
                        <div class="list-heading-wrapper">
                            <div class="subtitle-2 fs-12 letter-space-4px text-color-white text-uppercase">Frequancy</div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="newsletter-cover">
                <div class="background-newsletter">
                    <div class="newsletter-overlay"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="blog-posts">
        <div class="wrapper">
            <div class="heading-grid">
                <div class="intro">
                    <div class="subtitle">
                        <span class="fs-12 letter-space-4px text-uppercase text-color-dark">Recent Posts</span>
                    </div>
                    <div class="title">
                        <h1 class="fs-48 text-color-dark">What's New</h1>
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