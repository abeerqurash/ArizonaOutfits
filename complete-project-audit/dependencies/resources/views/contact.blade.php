@extends('layouts.app')

@section('title', 'Contact')
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

            <div class="content-4 content"></div>

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
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
                                <div class="subtitle">Start The Conversation</div><div class="dark-dot"></div>
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
                    <form action="{{ url('/contact-form-submit') }}" method="POST" id="contact-page-form">
                        @csrf
                        <input type="hidden" name="formSource" value="contact">

                        <div class="contact-page-type-selector" aria-label="Enquiry type">
                            <label class="contact-page-type-option">
                                <input
                                    type="radio"
                                    name="formType"
                                    value="Contact Us"
                                    {{ old('formType', 'Contact Us') === 'Contact Us' ? 'checked' : '' }}
                                    required
                                >
                                <span>Contact Us</span>
                            </label>

                            <label class="contact-page-type-option">
                                <input
                                    type="radio"
                                    name="formType"
                                    value="PR"
                                    {{ old('formType') === 'PR' ? 'checked' : '' }}
                                    required
                                >
                                <span>PR</span>
                            </label>
                        </div>

                        @if ($errors->any())
                            <div class="contact-page-errors">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="field-wrapper">
                            <label for="contact-full-name" class="field-label">Name</label>

                            <input
                                type="text"
                                id="contact-full-name"
                                class="text-field"
                                placeholder="Your Full Name"
                                name="fullName"
                                value="{{ old('fullName') }}"
                                maxlength="255"
                                autocomplete="name"
                                required
                            >

                            <input
                                type="text"
                                id="contact-subject"
                                class="text-field"
                                placeholder="Subject"
                                name="subject"
                                value="{{ old('subject') }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="field-wrapper">
                            <label for="contact-email" class="field-label-2">E-Mail</label>
                            <label for="contact-phone" class="field-label-2">Phone Number</label>

                            <input
                                type="email"
                                id="contact-email"
                                class="text-field email-address"
                                placeholder="E-Mail Address"
                                name="emailAddress"
                                value="{{ old('emailAddress') }}"
                                maxlength="255"
                                autocomplete="email"
                                required
                            >

                            <input
                                type="tel"
                                id="contact-phone"
                                class="text-field phone-number"
                                placeholder="Phone Number"
                                name="phoneNumber"
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
                            id="contact-page-pr-fields"
                            class="contact-page-pr-fields"
                            style="{{ old('formType') === 'PR' ? '' : 'display:none;' }}"
                        >
                            <div class="field-wrapper">
                                <label for="contact-pr-organization" class="field-label">PR Details</label>

                                <input
                                    type="text"
                                    id="contact-pr-organization"
                                    class="text-field"
                                    placeholder="Publication / Company / Agency"
                                    name="prOrganization"
                                    value="{{ old('prOrganization') }}"
                                    maxlength="255"
                                    data-contact-pr-required
                                >

                                <select
                                    id="contact-pr-enquiry-type"
                                    class="text-field contact-page-select"
                                    name="prEnquiryType"
                                    data-contact-pr-required
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
                                <label for="contact-pr-website" class="field-label">Website / Social Profile</label>

                                <input
                                    type="url"
                                    id="contact-pr-website"
                                    class="text-field contact-page-pr-website"
                                    placeholder="https://example.com"
                                    name="prWebsite"
                                    value="{{ old('prWebsite') }}"
                                    maxlength="2048"
                                >
                            </div>
                        </div>

                        <div class="field-wrapper">
                            <label for="contact-message" class="field-label">Message</label>

                            <textarea
                                id="contact-message"
                                name="messageBox"
                                class="text-field message-box"
                                placeholder="Message"
                                maxlength="5000"
                            >{{ old('messageBox') }}</textarea>
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

<style>
/*
|--------------------------------------------------------------------------
| Contact page Contact / PR controls
|--------------------------------------------------------------------------
| These styles are intentionally scoped to the Contact page form so the
| existing light Contact page design remains unchanged elsewhere.
*/
#contact-page-form .contact-page-type-selector {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 28px;
    padding-bottom: 18px;
    border-bottom: 1px solid rgba(23, 32, 51, 0.14);
}

#contact-page-form .contact-page-type-option {
    position: relative;
    display: inline-flex;
    align-items: center;
    margin: 0;
    cursor: pointer;
}

#contact-page-form .contact-page-type-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

#contact-page-form .contact-page-type-option span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 18px;
    border: 1px solid rgba(23, 32, 51, 0.22);
    border-radius: 999px;
    background: transparent;
    color: #172033;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    transition: background-color .2s ease, color .2s ease, border-color .2s ease;
}

#contact-page-form .contact-page-type-option:hover span,
#contact-page-form .contact-page-type-option input:focus-visible + span {
    border-color: #172033;
}

#contact-page-form .contact-page-type-option input:checked + span {
    border-color: #172033;
    background: #172033;
    color: #ffffff;
}

#contact-page-form .contact-page-errors {
    margin-bottom: 22px;
    padding: 12px 14px;
    border: 1px solid #fecaca;
    background: #fff7f7;
    color: #b42318;
    font-size: 13px;
    line-height: 1.6;
}

#contact-page-form .contact-page-pr-fields {
    display: grid;
    gap: 0;
}

#contact-page-form .contact-page-select {
    width: 100%;
    cursor: pointer;
}

#contact-page-form .contact-page-select option {
    background: #ffffff;
    color: #172033;
    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
}

#contact-page-form .contact-page-pr-website {
    grid-column: 1 / -1;
}

/* Preserve the existing international telephone input while keeping it aligned. */
#contact-page-form .iti {
    width: 100%;
}

#contact-page-form .iti__country-list {
    max-height: 260px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999;
}

@media (max-width: 767px) {
    #contact-page-form .contact-page-type-selector {
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    #contact-page-form .contact-page-type-option span {
        min-height: 38px;
        padding: 0 15px;
    }

    #contact-page-form .iti__country-list {
        max-width: calc(100vw - 32px);
        max-height: 220px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('contact-page-form');

    if (!form) {
        return;
    }

    const typeInputs = form.querySelectorAll('input[name="formType"]');
    const prFields = form.querySelector('#contact-page-pr-fields');
    const prRequiredFields = form.querySelectorAll('[data-contact-pr-required]');

    function updateContactPageType() {
        const selected = form.querySelector('input[name="formType"]:checked');
        const isPr = selected && selected.value === 'PR';

        if (prFields) {
            prFields.style.display = isPr ? 'grid' : 'none';
        }

        prRequiredFields.forEach(function (field) {
            field.required = isPr;
            field.disabled = !isPr;
        });
    }

    typeInputs.forEach(function (input) {
        input.addEventListener('change', updateContactPageType);
    });

    form.addEventListener('submit', updateContactPageType);

    updateContactPageType();
});
</script>

@endsection
