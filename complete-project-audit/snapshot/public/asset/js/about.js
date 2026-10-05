

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
// button-style-1

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
        scaleValue = 1 - (scrollTop * 0.00004); // slight zoom out
    }

    heroImage.style.transform =
        `translate3d(0, ${translateY}px, 0) scale(${scaleValue})`;

    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});

// services-card-transition

const serviceCards = document.querySelectorAll('.services .service-parent');

serviceCards.forEach(card => {
    const circle = card.querySelector('.service-circle');
    const button = card.querySelector('.card-button a');

    card.addEventListener('mouseenter', () => {

        // Expand circle smoothly
        circle.style.transform =
            'translate3d(-50%, 50%, 0) scale3d(20, 20, 1)';

        // Scale whole card slightly
        card.style.transform =
            'translate3d(0px, 0px, 0px) scale3d(1.05, 1.05, 1)';

        // Show button
        button.style.transform =
            'translate3d(0, 0, 0)';
        button.style.opacity = '1';
    });

    card.addEventListener('mouseleave', () => {

        // Reset circle
        circle.style.transform =
            'translate3d(0vw, 0vw, 0px) scale3d(1, 1, 1)';

        // Reset card scale
        card.style.transform =
            'translate3d(0px, 0px, 0px) scale3d(1, 1, 1)';

        // Hide button
        button.style.transform =
            'translate3d(0px, 100%, 0px) skew(0deg, 10deg)';
        button.style.opacity = '0';
    });
});

// about-info

const aboutSection = document.querySelector('.about');
const banner = document.querySelector('.about .banner-cover');
const aboutInfo = document.querySelector('.about .about-info');

window.addEventListener('scroll', () => {
    const scrollY = window.pageYOffset;
    const sectionTop = aboutSection.offsetTop;
    const sectionHeight = aboutSection.offsetHeight;
    const windowHeight = window.innerHeight;

    if (
        scrollY + windowHeight > sectionTop &&
        scrollY < sectionTop + sectionHeight
    ) {
        const relativeScroll = scrollY - sectionTop;

        // Movement
        const bannerMove = relativeScroll * -0.1;
        const infoMove = relativeScroll * 0.08;

        // Small zoom amount
        let zoom = 1 + (relativeScroll * 0.00025);

        // Prevent zoom going below 1
        zoom = Math.max(1, zoom);

        banner.style.transform =
            `translate3d(0px, ${bannerMove}px, 0px)
             scale3d(${zoom}, ${zoom}, 1)
             rotateX(0deg) rotateY(0deg) rotateZ(0deg)
             skew(0deg, 0deg)`;

        aboutInfo.style.transform =
            `translate3d(0px, ${infoMove}px, 0px)
             scale3d(1, 1, 1)
             rotateX(0deg) rotateY(0deg) rotateZ(0deg)
             skew(0deg, 0deg)`;
    }
});

// project-cards

const whatIDoCards = document.querySelectorAll('.what-i-do .card');

whatIDoCards.forEach(card => {
    const circle = card.querySelector('.card-circle');

    card.addEventListener('mouseenter', () => {

        // Expand circle from top-right
        circle.style.transform =
            'translate3d(-50%, 50%, 0) scale3d(20, 20, 1)';

        // Slight scale on card (optional – remove if not needed)
        card.style.transform =
            'translate3d(0px, 0px, 0px) scale3d(1.05, 1.05, 1)';
    });

    card.addEventListener('mouseleave', () => {

        // Reset circle
        circle.style.transform =
            'translate3d(0vw, 0vw, 0px) scale3d(1, 1, 1) rotateX(0deg) rotateY(0deg) rotateZ(0deg) skew(0deg, 0deg)';

        // Reset card
        card.style.transform =
            'translate3d(0px, 0px, 0px) scale3d(1, 1, 1)';
    });
});

// what-i-do-background

const whatSection = document.querySelector('.what-i-do');
const bgBanner = document.querySelector('.what-i-do .background-banner');

window.addEventListener('scroll', () => {
    const scrollY = window.pageYOffset;
    const sectionTop = whatSection.offsetTop;
    const sectionHeight = whatSection.offsetHeight;
    const windowHeight = window.innerHeight;

    // Only run when section is visible
    if (
        scrollY + windowHeight > sectionTop &&
        scrollY < sectionTop + sectionHeight
    ) {
        const relativeScroll = scrollY - sectionTop;

        // Slow movement intensity (adjust if needed)
        const move = relativeScroll * -0.18;

        bgBanner.style.transform =
            `translate3d(0px, ${move}px, 0px)
             scale3d(1, 1.5, 1)
             rotateX(0deg) rotateY(0deg) rotateZ(0deg)
             skew(0deg, 0deg)`;
    }
});

// home-recet-projects
const projectItems = document.querySelectorAll('.recent-projects .list-item');

projectItems.forEach(item => {

    const projectImage = item.querySelector('.porject-image');
    const backgroundImage = item.querySelector('.background-image');
    const circle = item.querySelector('.project-card-circle');
    const linkWrapper = item.querySelector('.link-wrapper');

    item.addEventListener('mouseenter', () => {

        // Scale outer container slightly smaller
        projectImage.style.transform =
            'translate3d(0px,0px,0px) scale3d(0.95,0.95,1)';

        // Scale background slightly bigger
        backgroundImage.style.transform =
            'translate3d(0px,0px,0px) scale3d(1.05,1.05,1)';

        // Expand circle dramatically
        circle.style.transform =
            'translate3d(6vw, -6vw, 0px) scale3d(9,9,1)';

        circle.style.width = '55vw';
        circle.style.height = '55vw';

        // Show link smoothly
        linkWrapper.style.transform =
            'translate3d(0px,0px,0px) scale3d(1,1,1) rotateX(0deg) rotateY(0deg) rotateZ(0deg) skew(0deg,0deg)';
        linkWrapper.style.opacity = '1';
    });

    item.addEventListener('mouseleave', () => {

        projectImage.style.transform =
            'translate3d(0px,0px,0px) scale3d(1,1,1)';

        backgroundImage.style.transform =
            'translate3d(0px,0px,0px) scale3d(1,1,1)';

        circle.style.transform =
            'translate3d(0vw,0vw,0px) scale3d(1,1,1)';
        circle.style.width = '6vw';
        circle.style.height = '6vw';

        linkWrapper.style.transform =
            'translate3d(0px,48px,0px) scale3d(0.8,0.8,1) skew(0deg,20deg)';
        linkWrapper.style.opacity = '0';
    });

});


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