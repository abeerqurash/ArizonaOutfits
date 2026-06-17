@extends('layouts.app')

@section('title', 'Web Developments') {{-- or $title if passed from controller --}}
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
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">Services</div>
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
    <div class="blog-posts">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent">
                    <div class="parent-wrapper">
                        <div class="about-info-parent">
                            <div class="about-info-child">
                                <div class="about-info-image ">
                                    <div class="about-background-image image-1">
                                        <div class="image-overlay"></div>
                                        <div class="about-card-circle"></div>
                                    </div>
                                </div>
                                <div class="about-info">
                                    <div class="my-info">
                                        <div class="title fs-24 text-capitalize text-color-white">Abeer Khan</div>
                                        <div class="tag fs-12 text-color-white text-uppercase letter-space-4px">Founder</div>
                                    </div>
                                    <div class="social-item">
                                        <div class="social-item-wrapper">
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-facebook-f text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-instagram text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-x-twitter text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-linkedin text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-youtube text-color-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="about-info-parent auto-margin">
                            <div class="about-info-child">
                                <div class="about-info-content">
                                    <div class="heading fs-32 text-color-dark">Web Developer</div>
                                    <div class="description fs-18-400 text-color-body">I am a web developer with three years of hands-on experience building things that actually work in the real world. From custom web development and API integration to automation systems and booking platforms — I have worked across a pretty wide range of projects that kept me sharp and constantly learning. Laravel and WordPress are my playgrounds, and I genuinely enjoy the problem-solving that comes with every new build. If it lives on the web, chances are I have built something like it, broken it, fixed it, and made it better than it was before.</div>
                                    <a href="/abeerkhan" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">Read About</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="about-info-parent auto-margin">
                            <div class="about-info-child">
                                <div class="about-info-content">
                                    <div class="heading fs-32 text-color-dark">Mission</div>
                                    <div class="description fs-18-400 text-color-body">Honestly, we just want people to stop feeling overwhelmed by the internet. There is too much noise, too many unreliable sources, and never enough honest answers in one place. That is exactly why Ideostream exists. We cover everything — not because we are trying to be everything to everyone, but because we genuinely believe curious people deserve better. Better writing, better research, and a place that respects your time and intelligence every single time you visit.</div>
                                </div>
                            </div>
                        </div>
                        <div class="about-info-parent">
                            <div class="about-info-child">
                                <div class="about-info-image ">
                                    <div class="about-background-image image-2">
                                        <div class="image-overlay"></div>
                                        <div class="about-card-circle"></div>
                                    </div>
                                </div>
                                <div class="about-info">
                                    <div class="my-info">
                                    </div>
                                    <div class="social-item">
                                        <div class="social-item-wrapper">
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-facebook-f text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-instagram text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-x-twitter text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-linkedin text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-youtube text-color-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="about-info-parent">
                            <div class="about-info-child">
                                <div class="about-info-image ">
                                    <div class="about-background-image image-3">
                                        <div class="image-overlay"></div>
                                        <div class="about-card-circle"></div>
                                    </div>
                                </div>
                                <div class="about-info">
                                    <div class="my-info">
                                    </div>
                                    <div class="social-item">
                                        <div class="social-item-wrapper">
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-facebook-f text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-instagram text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-x-twitter text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-linkedin text-color-dark"></i>
                                            </a>
                                            <a href="#" class="icon-and-title text-decoration-none">
                                                <i class="fa-brands fa-youtube text-color-dark"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="about-info-parent auto-margin">
                            <div class="about-info-child">
                                <div class="about-info-content">
                                    <div class="heading fs-32 text-color-dark">Vision</div>
                                    <div class="description fs-18-400 text-color-body">We are not chasing viral moments or trending clickbait. We are building something we actually want to use ourselves — a place where you can land on any topic and trust what you are reading. Five years from now, ten years from now, we want Ideostream to be the first name that comes to mind when someone wants to understand something properly. Not the loudest voice on the internet — just the most honest one people keep coming back to.</div>
                                </div>
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
                                <div class="list-item">
                                    <a href="category/ai-machine-learning" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">AI Machine Learning</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="/category/cybersecurity-data-privacy" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Cybersecurity & Data Privacy</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="/category/fintech-digital-banking" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Fintech & Digital Banking</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="/category/climate-tech-green-energy" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Climate Tech & Green Energy</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="/category/health-tech-digital-wellness" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Health Tech & Digital Wellness</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="/category/cryptocurrency-web3" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Cryptocurrency & Web3</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="/category/e-commerce-retail-tech" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">E-Commerce & Retail Tech</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
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
                            <p class="text-color-white fs-12 text-uppercase letter-space-4px">IDEOSTREAM, CEO</p>
                        </div>
                    </div>
                    <div class="video-link-wrapper">
                        <a href="tel:923452212770" class="btn-style-1 fs-12 text-color-white justify-self-start">
                            <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
    <div class="blog-posts service-content">
        <div class="wrapper">
            <div class="post-and-categories">
                <div class="post-cards-parent background-color-pinstrip">
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
                        <div class="list-heading">
                            <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-dark">What we Did</div>
                        </div>
                        <div class="categories-list">
                            <div class="list-wrapper">
                                <div class="list-item">
                                    <div class="list-item-inner fs-18-400 text-color-body">
                                        Lorem ipsum dolor sit amet.
                                    </div>
                                </div>
                                <div class="list-item">
                                    <div class="list-item-inner fs-18-400 text-color-body">
                                        Lorem ipsum dolor sit amet.
                                    </div>
                                </div>
                                <div class="list-item">
                                    <div class="list-item-inner fs-18-400 text-color-body">
                                        Lorem ipsum dolor sit amet.
                                    </div>
                                </div>
                                <div class="list-item">
                                    <div class="list-item-inner fs-18-400 text-color-body">
                                        Lorem ipsum dolor sit amet.
                                    </div>
                                </div>
                                <div class="list-item">
                                    <div class="list-item-inner fs-18-400 text-color-body">
                                        Lorem ipsum dolor sit amet.
                                    </div>
                                </div>
                                <div class="list-item">
                                    <div class="list-item-inner fs-18-400 text-color-body">
                                        Lorem ipsum dolor sit amet.
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <a href="/services" class="btn-style-1 fs-12 text-color-white justify-self-start">
                                <div class="button-text text-uppercase letter-space-3px">View All Services</div>
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
                </div>
            </div>
        </div>
    </div>
</div>

@endsection