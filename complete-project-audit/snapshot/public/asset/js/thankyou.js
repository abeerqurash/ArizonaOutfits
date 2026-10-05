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

const scrollingItem = document.querySelector('.scrolling-item');

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