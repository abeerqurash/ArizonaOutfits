@extends('layouts.portfolio')

@section('title', 'Abeer Khan') {{-- or $title if passed from controller --}}
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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">INSIDE innovation</p>
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
            <div class="content-5 content">
                <a href="" class="projects text-decoration-none">
                    <div class="description">
                        <div class="subtitle text-color-dark fs-12 text-uppercase letter-space-4px">Karachi, Sindh</div>
                        <div class="title text-color-dark fs-18">Pakistan</div>
                    </div>
                    <img src="{{ asset('asset/media/right-arrow.png') }}" alt="" class="right-arrow">
                </a>
            </div>
            <div class="feature">
                <p class="fs-11 text-color-dark text-uppercase letter-space-3px rotate-text-90minus">Featured</p>
            </div>


        </div>
    </div>
    <div class="services">
        <div class="service-wrapper">
            <div class="service-heading-button">
                <div class="title-wrapper">
                    <div class="sub-title fs-12 letter-space-4px text-uppercase"><span>Services</span></div>
                    <div class="title">
                        <h2 class="fs-48 text-color-dark">I do it best</h2>
                    </div>
                </div>
                <div class="view-all-services">
                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                                <p class="text-color-body fs-16">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugiat modi voluptatem est rem aliquid enim, unde odit numquam deserunt cupiditate?</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                                <p class="text-color-body fs-16">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugiat modi voluptatem est rem aliquid enim, unde odit numquam deserunt cupiditate?</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                                <p class="text-color-body fs-16">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugiat modi voluptatem est rem aliquid enim, unde odit numquam deserunt cupiditate?</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                                <p class="text-color-body fs-16">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugiat modi voluptatem est rem aliquid enim, unde odit numquam deserunt cupiditate?</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                                <h3 class="fs-24 text-color-dark">integiration</h3>
                                <p class="text-color-body fs-16">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugiat modi voluptatem est rem aliquid enim, unde odit numquam deserunt cupiditate?</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                                <p class="text-color-body fs-16">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Fugiat modi voluptatem est rem aliquid enim, unde odit numquam deserunt cupiditate?</p>
                            </div>
                            <div class="card-button">
                                <div class="button-parent">
                                    <a href="#" class="btn-style-2 fs-12 text-color-white justify-self-start">
                                        <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
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
                        <p class="sub-title text-color-white fs-12 text-uppercase letter-space-4px">About Abeer</p>
                    </div>
                    <div class="about-content">
                        <h1 class="fs-48 text-color-white">We're innovating the way companies reinvent their office spaces for the remote workforce.</h1>
                        <div class="content">
                            <p class="text-color-white fs-12 text-uppercase letter-space-4px">JESSICA POINT, CEO</p>
                        </div>
                    </div>
                    <div class="video-link-wrapper">
                        <a href="" class="link-wrapper moving-circle">
                            <i class="fa-solid fa-play"></i>
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
                                <span class="fs-12 text-uppercase text-color-white letter-space-4px">Explore Our Work</span>
                            </div>
                            <div class="title">
                                <h1 class="fs-48 text-color-white">See what i can do for you.</h1>
                            </div>
                        </div>
                        <div class="all-projects">
                            <a href="tel:+923123743890" class="btn-style-1 fs-12 text-color-white justify-self-start">
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
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Lorem, ipsum.</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Lorem, ipsum dolor.</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="#brands" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="project-bottom-info">
                                    <div class="project-date fs-12 letter-space-4px text-uppercase text-color-white">06.14.24</div>
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
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Lorem, ipsum.</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Lorem, ipsum dolor.</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="#brands" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="project-bottom-info">
                                    <div class="project-date fs-12 letter-space-4px text-uppercase text-color-white">06.14.24</div>
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
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Lorem, ipsum.</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Lorem, ipsum dolor.</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="#brands" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="project-bottom-info">
                                    <div class="project-date fs-12 letter-space-4px text-uppercase text-color-white">06.14.24</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-4">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-4">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Lorem, ipsum.</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Lorem, ipsum dolor.</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="#brands" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="project-bottom-info">
                                    <div class="project-date fs-12 letter-space-4px text-uppercase text-color-white">06.14.24</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-5">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-5">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Lorem, ipsum.</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Lorem, ipsum dolor.</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="#brands" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="project-bottom-info">
                                    <div class="project-date fs-12 letter-space-4px text-uppercase text-color-white">06.14.24</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="list-item item-6">
                        <div class="project-item">
                            <div class="porject-image">
                                <div class="background-image image-6">
                                    <div class="image-overlay"></div>
                                    <div class="project-card-circle"></div>
                                </div>
                            </div>
                            <div class="project-info">
                                <div class="project-top-info">
                                    <div class="subtitle-wrapper">
                                        <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">Lorem, ipsum.</div>
                                    </div>
                                    <h3 class="title fs-24 text-color-white text-capitalize">Lorem, ipsum dolor.</h3>
                                </div>
                                <div class="project-link">
                                    <div class="link-wrapper">
                                        <a href="#brands" class="moving-circle">
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="project-bottom-info">
                                    <div class="project-date fs-12 letter-space-4px text-uppercase text-color-white">06.14.24</div>
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
                                        <div class="first-name fs-48 text-capitalize text-color-white">Peter</div>
                                        <div class="last-name fs-48 text-capitalize text-color-white">Parker</div>
                                    </div>
                                    <div class="subtitle">
                                        <div class="location-title fs-12 letter-space-4px text-color-white text-uppercase">Los Angeles, California</div>
                                    </div>
                                </div>
                                <div class="testimonial-column testimonial-description">
                                    <div class="description-wrapper">
                                        <div class="description text-none fs-16 text-color-white">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Aliquid quia id magni beatae provident officiis impedit consequuntur excepturi.

                                            Vero magni est perferendis nihil neque amet dolorem a omnis maxime accusamus expedita, laudantium corporis. Minima, accusamus ducimus eius at sed ut ullam distinctio error nesciunt, nulla eligendi quas magni impedit nihil!</div>
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
                            <div class="subtitle fs-12 text-uppercase letter-space-4px text-color-dark">Newsletter</div>
                        </div>
                        <div class="heading-wrapper">
                            <div class="heading fs-32 text-color-dark text-none">Everything interior design in your inbox.</div>
                        </div>
                    </div>
                    <div class="checklist">
                        <div class="checklist-item item-1">
                            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="check-content text-color-body fs-16">Lorem ipsum dolor sit amet consectetur.</div>
                        </div>
                        <div class="checklist-item item-1">
                            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="check-content text-color-body fs-16">Lorem ipsum dolor sit amet consectetur.</div>
                        </div>
                        <div class="checklist-item item-1">
                            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
                            <div class="check-content text-color-body fs-16">Lorem ipsum dolor sit amet consectetur.</div>
                        </div>
                    </div>
                </div>
                <div class="portfolio-form">
                    <form action="" class="home-form" id="home-form">
                        <div class="form-radio-column">
                            <div class="radio-menu">
                                <div class="menu-list">
                                    <label class="menu-item menu-item-1">
                                        <div class="raido-button radio-btn-ctr radio-checked"></div>
                                        <input type="radio" class="radio-input contact-us-radio" id="contactUsRadio" value="formType">
                                        <span class="list-item-text" for="formType_1">Contact Us</span>
                                    </label>
                                    <label class="menu-item menu-item-1">
                                        <div class="raido-button radio-btn-ctr"></div>
                                        <input type="radio" class="radio-input prContacts-radio" id="prContacts" value="formType">
                                        <span class="list-item-text" for="formType_2">PR</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-column right">
                            <div class="form">
                                <div class="field-wrapper">
                                    <label for="name" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">Name</label>
                                    <input type="text" name="firstName" id="firstName" placeholder="First Name" class="name-field input-field">
                                    <input type="text" name="lastName" id="lastName" placeholder="Last Name" class="name-field input-field">
                                </div>
                                <div class="field-wrapper">
                                    <label for="name" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Email</label>
                                    <label for="name" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Phone Number</label>
                                    <input type="email" name="emailAddress" id="emailAddress" placeholder="Your Email" class="email-field input-field">
                                    <input type="tel" name="phoneNumber" id="phoneNumber" placeholder="Phone Number" class="phone-field input-field">
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
                <div class="post-categories">
                    <div class="categories-and-title">
                        <div class="list-heading">
                            <div class="subtitle fs-12 letter-space-4px text-uppercase text-color-dark">Popular Categories</div>
                        </div>
                        <div class="categories-list">
                            <div class="list-wrapper">
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Web Development</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">AI</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Modern Technologies</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Crypto Currencies</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Wordpress Development</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Technical Errors</div>
                                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                                    </a>
                                </div>
                                <div class="list-item">
                                    <a href="" class="menu-item fs-18-400 text-color-body">
                                        <div class="list-item-text">Robotics</div>
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
</div>

@endsection
