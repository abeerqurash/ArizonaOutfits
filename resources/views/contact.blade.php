@extends('layouts.app')

@section('title', 'Contact') {{-- or $title if passed from controller --}}
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
                <p class="fs-12 text-color-white text-uppercase letter-space-4px">Contact Us</p>
            </div>
            <div class="content-2 content">
                <a href="#brands" class="moving-circle">
                    <i class="fa-solid fa-arrow-down-long"></i>
                </a>
            </div>
            <div class="content-3 content">
                <h1 class="fs-78 text-color-white">Start Your <br> Idea Journey</h1>
                <a href="tel:923123743890" class="btn-style-1 fs-12 text-color-white justify-self-start">
                    <div class="button-text text-uppercase letter-space-3px">schedule a call</div>
                </a>
            </div>
            <div class="content-4 content">

            </div>
            <div class="home content">
                <div class="header-subtitle">
                    <div class="subtitle fs-12 text-uppercase text-color-white letter-space-4px">Talk To Us Today</div>
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
    <div class="form-section no-top-padding">
        <div class="wrapper">
            <div class="page-description-wrapper">
                <div class="form-description-box">
                    <form action="{{ url('/contact-form-submit') }}" method="POST">
    @csrf
                        <div class="field-wrapper">
                            <label for="" class="field-label">Name</label>
                            <input type="text" class="text-field" placeholder="Your Full Name" name="fullName" required>
                            <input type="text" class="text-field" placeholder="Subject" name="subject" required>
                        </div>
                        <div class="field-wrapper">
                            <label for="" class="field-label-2">E-Mail</label>
                            <label for="" class="field-label-2">Phone Number</label>
                            <input type="email" class="text-field email-address" placeholder="E-Mail Address" name="emailAddress" required>
                            <input type="tel" class="text-field phone-number" placeholder="Phone Number" name="phoneNumber" required>
                        </div>
                        <div class="field-wrapper">
                            <label for="" class="field-label">Message</label>
                            <textarea name="messageBox" class="text-field message-box" placeholder="Message"></textarea>
                        </div>
                        <input type="submit" value="Submit" class="submit-button">

                    </form>
                </div>
            </div>
            
            <div class="quick-links-box">
                <div class="sticky-links">
                    <div class="link-header-wrapper">
                        <div class="subtitle">Quick Links</div>
                    </div>
                    <div class="link-menu-list">
                        <a href="https://calendly.com/abeerkhan" class="menu-list-item">
                            <div class="quick-item-list">Scedule a meeting</div>
                            <i class="fa-solid fa-arrow-right-long quick-item-arrow"></i>
                        </a>
                        <a href="mailto:aq5431231@gmail.com" class="menu-list-item">
                            <div class="quick-item-list">aq5431231@gmail.com</div>
                            <i class="fa-solid fa-arrow-right-long quick-item-arrow"></i>
                        </a>
                        <a href="tel:+923123743890" class="menu-list-item">
                            <div class="quick-item-list">+92 312-3743890</div>
                            <i class="fa-solid fa-arrow-right-long quick-item-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection