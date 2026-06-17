

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
            <div class="subtitle fs-12 text-uppercase letter-space-4px text-color-dark">
                Get A Quote
            </div>
        </div>

        <div class="heading-wrapper">
            <div class="heading fs-32 text-color-dark text-none">
                Let's Build Something Great Together.
            </div>
        </div>
    </div>

    <div class="checklist">
        <div class="checklist-item item-1">
            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
            <div class="check-content text-color-body fs-16">
                Tell us about your dream project vision.
            </div>
        </div>

        <div class="checklist-item item-1">
            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
            <div class="check-content text-color-body fs-16">
                We craft solutions tailored to your needs.
            </div>
        </div>

        <div class="checklist-item item-1">
            <div class="check-icon"><i class="fa-solid fa-check"></i></div>
            <div class="check-content text-color-body fs-16">
                Get your free quote within 24 hours.
            </div>
        </div>
    </div>
</div>
                <div class="portfolio-form">
                    <form action="{{ url('/blog-form-submit') }}" method="POST" class="home-form blog-form-submission">
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
                        <input type="hidden" name="blog_title" value="{{ $post->title ?? 'No title found' }}">
                        <input type="hidden" name="blog_slug" value="{{ $post->slug ?? request()->segment(1) }}">
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