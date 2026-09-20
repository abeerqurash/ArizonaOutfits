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
                        <h2 class="fs-48 text-color-dark">Latest Products</h2>
                    </div>
                </div>
                <div class="view-all-services">
                    <a href="{{ route('products.index') }}" class="btn-style-2 fs-12 text-color-white justify-self-start">
                        <div class="button-text text-uppercase letter-space-3px">Shop Now</div>
                    </a>
                </div>
            </div>
            <div class="services-grid">

                @forelse($latestProducts as $product)

                @include('products.partials.product-card', [
                'product' => $product
                ])

                @empty

                <div class="w-100 text-center">
                    <p>No products available yet.</p>
                </div>

                @endforelse

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

                @forelse($popularProducts as $product)

                @include('products.partials.product-card', [
                'product' => $product
                ])

                @empty

                <div class="w-100 text-center">
                    <p>No popular products available yet.</p>
                </div>

                @endforelse

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
                        <input type="hidden" name="formSource" value="home">

                        <div class="form-radio-column">
                            <div class="radio-menu">
                                <div class="menu-list">
                                    <label class="menu-item menu-item-1">
                                        <div class="raido-button radio-btn-ctr {{ old('formType', 'Contact Us') === 'Contact Us' ? 'radio-checked' : '' }}"></div>
                                        <input
                                            type="radio"
                                            name="formType"
                                            class="radio-input contact-us-radio"
                                            id="contactUsRadio"
                                            value="Contact Us"
                                            {{ old('formType', 'Contact Us') === 'Contact Us' ? 'checked' : '' }}
                                            required
                                        >
                                        <span class="list-item-text">Contact Us</span>
                                    </label>

                                    <label class="menu-item menu-item-1">
                                        <div class="raido-button radio-btn-ctr {{ old('formType') === 'PR' ? 'radio-checked' : '' }}"></div>
                                        <input
                                            type="radio"
                                            name="formType"
                                            class="radio-input prContacts-radio"
                                            id="prContacts"
                                            value="PR"
                                            {{ old('formType') === 'PR' ? 'checked' : '' }}
                                            required
                                        >
                                        <span class="list-item-text">PR</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-column right">
                            <div class="form">
                                @if ($errors->any())
                                    <div style="grid-column:1/-1;color:#fff;background:rgba(220,38,38,.18);border:1px solid rgba(255,255,255,.18);padding:12px 14px;border-radius:4px;">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="field-wrapper">
                                    <label for="fullname" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">Name</label>

                                    <input
                                        type="text"
                                        name="fullName"
                                        id="fullname"
                                        placeholder="Full Name"
                                        class="name-field input-field"
                                        value="{{ old('fullName') }}"
                                        maxlength="255"
                                        autocomplete="name"
                                        required
                                    >

                                    <input
                                        type="text"
                                        name="subject"
                                        id="subject"
                                        placeholder="Subject"
                                        class="name-field input-field"
                                        value="{{ old('subject') }}"
                                        maxlength="255"
                                        required
                                    >
                                </div>

                                <div class="field-wrapper">
                                    <label for="emailAddress" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Email</label>
                                    <label for="phoneNumber" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Phone Number</label>

                                    <input
                                        type="email"
                                        name="emailAddress"
                                        id="emailAddress"
                                        placeholder="Your Email"
                                        class="email-field input-field"
                                        value="{{ old('emailAddress') }}"
                                        maxlength="255"
                                        autocomplete="email"
                                        required
                                    >

                                    <input
                                        type="tel"
                                        name="phoneNumber"
                                        id="phoneNumber"
                                        placeholder="Phone Number"
                                        class="phone-field input-field"
                                        value="{{ old('phoneNumber') }}"
                                        inputmode="tel"
                                        maxlength="30"
                                        pattern="[0-9+() .\-]{7,30}"
                                        title="Enter a valid phone number using numbers and standard phone characters."
                                        autocomplete="tel"
                                        oninput="this.value=this.value.replace(/[^0-9+() .\-]/g,'')"
                                        required
                                    >
                                </div>

                                <div
                                    id="home-pr-fields"
                                    style="{{ old('formType') === 'PR' ? 'display:grid;' : 'display:none;' }}grid-row-gap:24px;"
                                >
                                    <div class="field-wrapper">
                                        <label for="prOrganization" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">PR Details</label>

                                        <input
                                            type="text"
                                            name="prOrganization"
                                            id="prOrganization"
                                            placeholder="Publication / Company / Agency"
                                            class="input-field"
                                            value="{{ old('prOrganization') }}"
                                            maxlength="255"
                                            data-pr-required
                                        >

                                        <select
                                            name="prEnquiryType"
                                            id="prEnquiryType"
                                            class="input-field"
                                            data-pr-required
                                            style="appearance:auto;"
                                        >
                                            <option value="">PR Enquiry Type</option>
                                            <option value="Press / Media Enquiry" {{ old('prEnquiryType') === 'Press / Media Enquiry' ? 'selected' : '' }}>Press / Media Enquiry</option>
                                            <option value="Interview Request" {{ old('prEnquiryType') === 'Interview Request' ? 'selected' : '' }}>Interview Request</option>
                                            <option value="Product Feature / Review" {{ old('prEnquiryType') === 'Product Feature / Review' ? 'selected' : '' }}>Product Feature / Review</option>
                                            <option value="Brand Collaboration" {{ old('prEnquiryType') === 'Brand Collaboration' ? 'selected' : '' }}>Brand Collaboration</option>
                                            <option value="Event / Appearance" {{ old('prEnquiryType') === 'Event / Appearance' ? 'selected' : '' }}>Event / Appearance</option>
                                            <option value="Other PR Enquiry" {{ old('prEnquiryType') === 'Other PR Enquiry' ? 'selected' : '' }}>Other PR Enquiry</option>
                                        </select>
                                    </div>

                                    <div class="field-wrapper">
                                        <label for="prWebsite" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">Website / Social Profile</label>

                                        <input
                                            type="url"
                                            name="prWebsite"
                                            id="prWebsite"
                                            placeholder="https://example.com"
                                            class="input-field"
                                            value="{{ old('prWebsite') }}"
                                            maxlength="2048"
                                            style="grid-column:1/-1;"
                                        >
                                    </div>
                                </div>

                                <textarea
                                    name="messageBox"
                                    id="messageBox"
                                    placeholder="Message"
                                    class="textarea-field input-field"
                                    rows="8"
                                    maxlength="5000"
                                >{{ old('messageBox') }}</textarea>

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




<style>
/* Homepage Contact / PR dropdown UI only */
#home-form-submission #prEnquiryType {
    width: 100%;
    min-height: 43px;
    padding: 0 38px 0 14px;
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-radius: 0;
    background-color: #1f2230;
    color: #ffffff;
    font: 12px;
    cursor: pointer;
}

#home-form-submission #prEnquiryType:focus {
    outline: none;
    border-color: #ffffff;
}

#home-form-submission #prEnquiryType option {
    background-color: #1f2230;
    color: #ffffff;
    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
    letter-spacing: inherit;
}

/* Keep intl-tel-input aligned with the existing dark phone field */
#home-form-submission .iti {
    width: 100%;
}

#home-form-submission .iti__flag-container {
    height: 100%;
}

#home-form-submission .iti__selected-flag {
    background: transparent;
}

#home-form-submission .iti__selected-flag:hover,
#home-form-submission .iti__selected-flag:focus,
#home-form-submission .iti__selected-flag[aria-expanded="true"] {
    background: rgba(255, 255, 255, 0.08);
}

#home-form-submission .iti__country-list {
    width: 335px;
    max-width: min(335px, calc(100vw - 32px));
    max-height: 260px;
    overflow-y: auto;
    overflow-x: hidden;
    background: #1f2230;
    border: 1px solid rgba(255, 255, 255, 0.24);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28);
    color: #ffffff;
    z-index: 9999;
}

#home-form-submission .iti__country {
    padding: 8px 10px;
    white-space: nowrap;
}

#home-form-submission .iti__country.iti__highlight {
    background: rgba(255, 255, 255, 0.12);
}

#home-form-submission .iti__country-name,
#home-form-submission .iti__dial-code {
    color: #ffffff;
}

@media (max-width: 767px) {
    #home-form-submission .iti__country-list {
        width: min(310px, calc(100vw - 32px));
        max-height: 220px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const homeForm = document.getElementById('home-form-submission');

    if (!homeForm) {
        return;
    }

    const contactRadio = homeForm.querySelector('#contactUsRadio');
    const prRadio = homeForm.querySelector('#prContacts');
    const prFields = homeForm.querySelector('#home-pr-fields');
    const prRequiredFields = homeForm.querySelectorAll('[data-pr-required]');

    function updatePrFields() {
        const isPr = prRadio && prRadio.checked;

        if (prFields) {
            prFields.style.display = isPr ? 'grid' : 'none';
        }

        prRequiredFields.forEach(function (field) {
            field.required = isPr;
            field.disabled = !isPr;
        });
    }

    /*
     * main.js controls these custom radio buttons by changing .checked
     * programmatically. A programmatic checked change does not reliably fire
     * the native "change" event, so listen to the visible menu items too.
     */
    const contactMenuItem = contactRadio ? contactRadio.closest('.menu-item') : null;
    const prMenuItem = prRadio ? prRadio.closest('.menu-item') : null;

    if (contactRadio) {
        contactRadio.addEventListener('change', updatePrFields);
    }

    if (prRadio) {
        prRadio.addEventListener('change', updatePrFields);
    }

    if (contactMenuItem) {
        contactMenuItem.addEventListener('click', function () {
            window.setTimeout(updatePrFields, 0);
        });
    }

    if (prMenuItem) {
        prMenuItem.addEventListener('click', function () {
            window.setTimeout(updatePrFields, 0);
        });
    }

    /*
     * Before submitting, run the state one final time so PR-only fields are
     * enabled/required only for PR and Contact Us can submit normally.
     */
    homeForm.addEventListener('submit', function () {
        updatePrFields();
    });

    updatePrFields();
});
</script>

{{-- ============================================================
     Shared product quick-view modal for Homepage product cards
     ============================================================ --}}
<div
    id="product-quick-view-modal"
    class="product-quick-view-modal"
    aria-hidden="true">

    <div
        class="product-quick-view-backdrop"
        data-close-product-popup></div>

    <div
        class="product-quick-view-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-view-product-title">

        <button
            type="button"
            class="product-popup-close fs-12 text-color-white justify-self-start"
            data-close-product-popup
            aria-label="Close product popup">
            <div class="button-text text-uppercase letter-space-3px">
                x
            </div>
        </button>

        <div
            id="product-quick-view-content"
            class="product-quick-view-content">
            <div class="product-popup-loader fs-16 text-uppercase letter-space-4px">
                Loading product...
            </div>
        </div>

    </div>

</div>

@push('page-scripts')
<script src="{{ asset('asset/js/product.js') }}"></script>
@endpush


@endsection