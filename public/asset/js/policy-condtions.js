// navbar-cover

const heroSection = document.querySelector(".home-hero");
const navCover = document.querySelector(".navigation-cover");
const navbar = document.querySelector(".navbar");

window.addEventListener("scroll", () => {
    const heroHeight = heroSection.offsetHeight;
    const scrollPosition = window.scrollY;

    if (scrollPosition > heroHeight / 2) {
        navCover.classList.add("active");
        navbar.classList.add("scrolled");
    } else {
        navCover.classList.remove("active");
        navbar.classList.remove("scrolled");
    }
});

const button = document.querySelectorAll('.moving-circle');

button.forEach(button => {

    button.addEventListener('mousemove', (e) => {
        const rect = button.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        // Adjust intensity here
        const moveX = x / 4;
        const moveY = y / 4;

        button.style.transform =
            `translate3d(${moveX}px, ${moveY}px, 0) scale(1.15)`;
    });

    button.addEventListener('mouseleave', () => {
        button.style.transform =
            'translate3d(0, 0, 0) scale(1)';
    });

});
const buttons = document.querySelectorAll(
  '.btn-style-1, .btn-style-2, .btn-style-3'
);

buttons.forEach(button => {
  const text = button.querySelector('.button-text');
  if (!text) return;

  button.addEventListener('mousemove', (e) => {
    const rect = button.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;

    text.style.transform =
      `translate3d(${x / 6}px, ${y / 6}px, 0) scale(1.12)`;
  });

  button.addEventListener('mouseleave', () => {
    text.style.transform =
      'translate3d(0, 0, 0) scale(1)';
  });
});

// home-hero-background

const heroImage = document.querySelector('.home-hero .background-hero');

let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollDiff = scrollTop - lastScrollTop;

    // Control movement intensity
    const translateY = scrollTop * -0;
    let scaleValue = 1;

    if (scrollDiff > 0) {
        // Scrolling DOWN
        scaleValue = 1 + (scrollTop * 0.0003); // slight zoom in
    } else {
        // Scrolling UP
        scaleValue = 1 - (scrollTop * 0.00002); // slight zoom out
    }

    heroImage.style.transform =
        `translate3d(0, ${translateY}px, 0) scale(${scaleValue})`;

    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});

// mega-menu

const menuBtn = document.querySelector('.menu-button');
const megaMenu = document.querySelector('.mega-menu');

menuBtn.addEventListener('click', function () {
    megaMenu.classList.toggle('active');
});

//menubutton

function toggleMenu() {
    const menuButton = document.querySelector('.menu-button');
    menuButton.classList.toggle('open');
}

document.addEventListener("DOMContentLoaded", function () {

    const elements = document.querySelectorAll(
        ".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent"
    );

    const observer = new IntersectionObserver((entries, observer) => {

        entries.forEach((entry) => {

            if (entry.isIntersecting) {

                // get index of current element
                const index = Array.from(elements).indexOf(entry.target);

                setTimeout(() => {
                    entry.target.classList.add("in-view");
                }, index * 150); // 150ms delay per item

                observer.unobserve(entry.target); // run only once
            }

        });

    }, {
        threshold: 0.2
    });

    elements.forEach(el => observer.observe(el));

});

document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("blogs-container");
  const loadMoreBtn = document.getElementById("load-more-btn");

  if (!loadMoreBtn) return;

  let page = parseInt(loadMoreBtn.dataset.page) || 1;
  const url = container.dataset.url;

  let loadLessBtn = document.getElementById("load-less-btn");

  // ===============================
  // LOAD MORE
  // ===============================
  loadMoreBtn.addEventListener("click", async () => {
    page++;

    try {
      const response = await fetch(`${url}?page=${page}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });

      const data = await response.text();

      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = data;

      const newPosts = tempDiv.querySelectorAll(".blogs-card-parent");

      // Append only cards
      newPosts.forEach(post => container.appendChild(post));

      // Check if more posts exist
      const hasMore = tempDiv.querySelector("#hasMore");

      if (!hasMore) {
        loadMoreBtn.style.display = "none";
      }

      if (page > 1) {
        showLoadLess();
      }

    } catch (err) {
      console.error(err);
    }
  });

  // ===============================
  // SHOW LOAD LESS BUTTON
  // ===============================
  function showLoadLess() {
    if (loadLessBtn) {
      loadLessBtn.style.display = "flex";
      return;
    }

    loadLessBtn = document.createElement("button");
    loadLessBtn.id = "load-less-btn";
    loadLessBtn.textContent = "LOAD LESS";
    loadLessBtn.className = "btn btn-secondary mt-3";

    container.parentNode.appendChild(loadLessBtn);

    loadLessBtn.addEventListener("click", resetToDefault);
  }

  // ===============================
  // LOAD LESS (RESET TO PAGE 1)
  // ===============================
  async function resetToDefault() {
    try {
      page = 1;

      const response = await fetch(`${url}?page=1`, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });

      const data = await response.text();

      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = data;

      const firstPagePosts = tempDiv.querySelectorAll(".blogs-card-parent");

      // Clear container safely
      container.innerHTML = "";

      // Insert only first 12 posts
      firstPagePosts.forEach(post => container.appendChild(post));

      // Reset buttons
      loadMoreBtn.style.display = "inline-block";

      if (loadLessBtn) {
        loadLessBtn.style.display = "none";
      }

    } catch (err) {
      console.error(err);
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {

    const elements = document.querySelectorAll(
        ".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent"
    );

    const observer = new IntersectionObserver((entries, observer) => {

        entries.forEach((entry) => {

            if (entry.isIntersecting) {

                // get index of current element
                const index = Array.from(elements).indexOf(entry.target);

                setTimeout(() => {
                    entry.target.classList.add("in-view");
                }, index * 150); // 150ms delay per item

                observer.unobserve(entry.target); // run only once
            }

        });

    }, {
        threshold: 0.2
    });

    elements.forEach(el => observer.observe(el));

});

document.addEventListener("DOMContentLoaded", () => {

    const slides = [
        {
            image: "asset/media/olivia-smith.webp",
            first: "Olivia",
            last: "Smith",
            location: "United States",
            testiDescription: "IdeoStream has become my daily morning read. The variety of topics covered is honestly impressive — from science to culture to personal development. It never feels like clickbait, just real, thoughtful writing."
        },
        {
            image: "asset/media/harry-wilson.webp",
            first: "Harry",
            last: "Wilson",
            location: "Germany",
            testiDescription: "I stumbled across IdeoStream while searching for an article on productivity, and I've been hooked ever since. The content is well-researched and the writers clearly know what they're talking about. Bookmarked for life."
        },
        {
            image: "asset/media/james-smith.webp",
            first: "James",
            last: "Smith",
            location: "UAE",
            testiDescription: "As someone who reads a lot of blogs for research, IdeoStream stands out. The articles are balanced and don't just skim the surface. I especially love the topics on psychology and human behavior. Would love even more academic references though!"
        }, {
            image: "asset/media/jeffrey-gordon.webp",
            first: "Jeffrey",
            last: "Gordon",
            location: "United Kingdom",
            testiDescription: "Running a business means I need to stay informed on a LOT of different things. IdeoStream literally covers everything — tech, business, lifestyle, world events. It's like having one smart friend who knows about everything."
        },
        {
            image: "asset/media/freya-brown.webp",
            first: "Freya",
            last: "Brown",
            location: "China",
            testiDescription: "The writing quality on IdeoStream is what got me. Clean, engaging, no fluff. You can tell the team genuinely cares about the content they publish. I've even shared several articles with my own readers."
        },
        {
            image: "asset/media/oliver-taylor.webp",
            first: "Oliver",
            last: "Taylor",
            location: "Canada",
            testiDescription: "I'm not the youngest person on the internet, but IdeoStream is easy to navigate and the articles are written in a way that's accessible without being dumbed down. Very refreshing compared to most news sites today."
        },
        {
            image: "asset/media/alex-friedman.webp",
            first: "Alex",
            last: "Friedman",
            location: "United Kingdom",
            testiDescription: "I used an IdeoStream article as a starting point for one of my essays and it led me down the best rabbit hole. The content sparks curiosity in a way most blogs just don't. Highly recommend to anyone who loves learning."
        }, {
            image: "asset/media/lily-taylor.webp",
            first: "Lily",
            last: "Taylor",
            location: "Poland",
            testiDescription: "I finally found a blog that covers topics I actually care about — health, parenting, world news, lifestyle — all in one place. IdeoStream feels personal, not robotic. My only wish is that they posted even more frequently!"
        },
        {
            image: "asset/media/thomas-williams.webp",
            first: "Thomas",
            last: "Williams",
            location: "Philippines",
            testiDescription: "From a professional standpoint, IdeoStream does something rare: it covers broad topics without losing depth. The editorial standards are clearly high. This is the kind of blog the internet actually needs more of."
        },
        {
            image: "asset/media/amelia-williams.webp",
            first: "Amelia",
            last: "Williams",
            location: "Canada",
            testiDescription: "I travel constantly and IdeoStream keeps me connected to ideas and conversations happening around the world. Whether I'm reading about culture, technology, or health — the content always feels relevant and timely. A gem of a website."
        }
    ];

    let index = 0;

    const bg = document.querySelector(".testimonial-background-image");

    const firstEl = document.querySelector(".first-name");
    const lastEl = document.querySelector(".last-name");
    const locationEl = document.querySelector(".location-title");
    const descEl = document.querySelector(".testimonial-description .description");

    const texts = [firstEl, lastEl, locationEl, descEl];

    const next = document.querySelector(".arrow-right");
    const prev = document.querySelector(".arrow-left");


    /* ---------- SMOOTH TRANSITIONS ---------- */

    bg.style.transition = "transform 1.2s cubic-bezier(.4,0,.2,1)";

    texts.forEach(t => {
        t.style.transition = "all .7s cubic-bezier(.4,0,.2,1)";
    });


    function animateOut() {
        bg.style.transform = "scale(1.2)";

        texts.forEach(t => {
            t.style.opacity = "0";
            t.style.transform =
                "translate3d(0,100%,0) scale3d(0,0,0) rotate(5deg)";
        });
    }


    function animateIn() {
        bg.style.transform = "scale(1)";

        texts.forEach(t => {
            t.style.opacity = "1";
            t.style.transform =
                "translate3d(0,0,0) scale3d(1,1,1) rotate(0)";
        });
    }


    function updateSlide() {
        animateOut();

        setTimeout(() => {
            const s = slides[index];

            bg.style.backgroundImage = `url("${s.image}")`;

            firstEl.textContent = s.first;
            lastEl.textContent = s.last;
            locationEl.textContent = s.location;
            descEl.textContent = s.testiDescription;   // ✅ fixed

            animateIn();
        }, 500);
    }


    next.addEventListener("click", () => {
        index = (index + 1) % slides.length;
        updateSlide();
    });

    prev.addEventListener("click", () => {
        index = (index - 1 + slides.length) % slides.length;
        updateSlide();
    });

    updateSlide();
});

// form-radio-check

const menuItems = document.querySelectorAll('.news-letter .menu-item');

menuItems.forEach(item => {
    const radioBtn = item.querySelector('.radio-btn-ctr');
    const radioInput = item.querySelector('.radio-input');

    item.addEventListener('click', () => {

        // Remove active from all
        document.querySelectorAll('.news-letter .radio-btn-ctr')
            .forEach(btn => btn.classList.remove('radio-checked'));

        document.querySelectorAll('.news-letter .radio-input')
            .forEach(input => input.checked = false);

        // Add active to clicked
        radioBtn.classList.add('radio-checked');
        radioInput.checked = true;

    });
});


// Newsletter

const newsletterBg = document.querySelector(".background-newsletter");

let lastScroll = window.scrollY;
let current = 0;
let target = 0;

const speed = 0.08;
const scale = 1.55; // default zoom level

window.addEventListener("scroll", () => {
    const scrollY = window.scrollY;
    const delta = scrollY - lastScroll;

    target += delta * 0.4;

    lastScroll = scrollY;
});

function animate() {
    current += (target - current) * speed;

    newsletterBg.style.transform =
        `translate3d(0, ${-current}px, 0) scale3d(${scale}, ${scale}, 1)`;

    requestAnimationFrame(animate);
}

animate();


const scrollingItem = document.querySelector('.scrolling-item');

if (scrollingItem) {

    let lastScrollY = window.scrollY;
    let targetX = 0;
    let currentX = 0;

    function animate() {
        currentX += (targetX - currentX) * 0.08;
        scrollingItem.style.transform = `translate3d(${currentX}px, 0, 0)`;
        requestAnimationFrame(animate);
    }

    window.addEventListener('scroll', () => {
        const scrollY = window.scrollY;
        const delta = scrollY - lastScrollY;

        targetX -= delta * 0.5;
        lastScrollY = scrollY;
    });

    animate();
}

document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector(".blog-form-submission");
    const firstNameInput = document.querySelector("input[name='fullName']");
    const lastNameInput = document.querySelector("input[name='subject']");
    const emailInput = document.querySelector(".email-field");
    const phoneInput = document.querySelector(".phone-field");

    if (!phoneInput) {
        console.log("Phone input not found");
        return;
    }

    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "auto",
        separateDialCode: true,
        geoIpLookup: function (callback) {
            fetch("https://ipapi.co/json")
                .then(res => res.json())
                .then(data => callback(data.country_code))
                .catch(() => callback("us"));
        },
        utilsScript:
            "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"
    });

});

document.addEventListener("DOMContentLoaded", function () {

    const forms = document.querySelectorAll('.blog-form-submission');

    forms.forEach(function(form) {

        form.addEventListener('submit', function () {

            const submitBtn = form.querySelector('[type="submit"]');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.value = 'Submitting...';
            }

        });

    });

});

