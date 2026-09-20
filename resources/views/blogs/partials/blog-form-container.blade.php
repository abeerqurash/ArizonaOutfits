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
            <form action="{{ url('/blog-form-submit') }}" method="POST" class="home-form" id="blog-form-submission">
                @csrf

                <input type="hidden" name="blog_title" value="{{ $post->title ?? 'No title found' }}">
                <input type="hidden" name="blog_slug" value="{{ $post->slug ?? request()->segment(1) }}">

                <div class="form-radio-column">
                    <div class="radio-menu">
                        <div class="menu-list">
                            <label class="menu-item menu-item-1">
                                <div class="raido-button radio-btn-ctr {{ old('formType', 'Contact Us') === 'Contact Us' ? 'radio-checked' : '' }}"></div>
                                <input
                                    type="radio"
                                    name="formType"
                                    class="radio-input contact-us-radio"
                                    id="blogContactUsRadio"
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
                                    id="blogPrContacts"
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
                            <label for="blogFullName" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">Name</label>

                            <input
                                type="text"
                                name="fullName"
                                id="blogFullName"
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
                                id="blogSubject"
                                placeholder="Subject"
                                class="name-field input-field"
                                value="{{ old('subject') }}"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="field-wrapper">
                            <label for="blogEmailAddress" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Email</label>
                            <label for="blogPhoneNumber" class="field-lablel-2 fs-12 letter-space-4px text-color-white text-uppercase">Phone Number</label>

                            <input
                                type="email"
                                name="emailAddress"
                                id="blogEmailAddress"
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
                                id="blogPhoneNumber"
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
                            id="blog-pr-fields"
                            style="{{ old('formType') === 'PR' ? 'display:grid;' : 'display:none;' }}grid-row-gap:24px;"
                        >
                            <div class="field-wrapper">
                                <label for="blogPrOrganization" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">PR Details</label>

                                <input
                                    type="text"
                                    name="prOrganization"
                                    id="blogPrOrganization"
                                    placeholder="Publication / Company / Agency"
                                    class="input-field"
                                    value="{{ old('prOrganization') }}"
                                    maxlength="255"
                                    data-blog-pr-required
                                >

                                <select
                                    name="prEnquiryType"
                                    id="blogPrEnquiryType"
                                    class="input-field"
                                    data-blog-pr-required
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
                                <label for="blogPrWebsite" class="field-lablel fs-12 letter-space-4px text-color-white text-uppercase">Website / Social Profile</label>

                                <input
                                    type="url"
                                    name="prWebsite"
                                    id="blogPrWebsite"
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
                            id="blogMessageBox"
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

<style>
/* Exact Homepage Contact / PR dropdown UI, scoped only to the Blog form */
#blog-form-submission #blogPrEnquiryType {
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

#blog-form-submission #blogPrEnquiryType:focus {
    outline: none;
    border-color: #ffffff;
}

#blog-form-submission #blogPrEnquiryType option {
    background-color: #1f2230;
    color: #ffffff;
    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
    letter-spacing: inherit;
}

/* Exact Homepage intl-tel-input treatment */
#blog-form-submission .iti {
    width: 100%;
}

#blog-form-submission .iti__flag-container {
    height: 100%;
}

#blog-form-submission .iti__selected-flag {
    background: transparent;
}

#blog-form-submission .iti__selected-flag:hover,
#blog-form-submission .iti__selected-flag:focus,
#blog-form-submission .iti__selected-flag[aria-expanded="true"] {
    background: rgba(255, 255, 255, 0.08);
}

#blog-form-submission .iti__country-list {
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

#blog-form-submission .iti__country {
    padding: 8px 10px;
    white-space: nowrap;
}

#blog-form-submission .iti__country.iti__highlight {
    background: rgba(255, 255, 255, 0.12);
}

#blog-form-submission .iti__country-name,
#blog-form-submission .iti__dial-code {
    color: #ffffff;
}

@media (max-width: 767px) {
    #blog-form-submission .iti__country-list {
        width: min(310px, calc(100vw - 32px));
        max-height: 220px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const blogForm = document.getElementById('blog-form-submission');

    if (!blogForm) {
        return;
    }

    const contactRadio = blogForm.querySelector('#blogContactUsRadio');
    const prRadio = blogForm.querySelector('#blogPrContacts');
    const prFields = blogForm.querySelector('#blog-pr-fields');
    const prRequiredFields = blogForm.querySelectorAll('[data-blog-pr-required]');

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
     * programmatically. Keep the same Homepage workaround here.
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

    blogForm.addEventListener('submit', function () {
        updatePrFields();
    });

    updatePrFields();
});
</script>
