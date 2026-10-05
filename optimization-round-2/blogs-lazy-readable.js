const heroSection = document.querySelector(".home-hero"), navCover = document.querySelector(".navigation-cover"), navbar = document.querySelector(".navbar");
window.addEventListener("scroll", () => {
  const e = heroSection.offsetHeight;
  window.scrollY > e / 2 ? (navCover.classList.add("active"), navbar.classList.add("scrolled")) : (navCover.classList.remove("active"), navbar.classList.remove("scrolled"));
});
const button = document.querySelectorAll(".moving-circle");
button.forEach((e) => {
  e.addEventListener("mousemove", (t) => {
    const o = e.getBoundingClientRect(), s = t.clientX - o.left - o.width / 2, n = t.clientY - o.top - o.height / 2, r = s / 4, c = n / 4;
    e.style.transform = `translate3d(${r}px, ${c}px, 0) scale(1.15)`;
  }), e.addEventListener("mouseleave", () => {
    e.style.transform = "translate3d(0, 0, 0) scale(1)";
  });
});
const buttons = document.querySelectorAll(".btn-style-1, .btn-style-2, .btn-style-3");
buttons.forEach((e) => {
  const t = e.querySelector(".button-text");
  t && (e.addEventListener("mousemove", (o) => {
    const s = e.getBoundingClientRect(), n = o.clientX - s.left - s.width / 2, r = o.clientY - s.top - s.height / 2;
    t.style.transform = `translate3d(${n / 6}px, ${r / 6}px, 0) scale(1.12)`;
  }), e.addEventListener("mouseleave", () => {
    t.style.transform = "translate3d(0, 0, 0) scale(1)";
  }));
});
const heroImage = document.querySelector(".home-hero .background-hero");
let lastScrollTop = 0;
window.addEventListener("scroll", () => {
  const e = window.pageYOffset || document.documentElement.scrollTop, t = e - lastScrollTop, o = e * -0;
  let s = 1;
  t > 0 ? s = 1 + e * 3e-4 : s = 1 - e * 2e-5, heroImage.style.transform = `translate3d(0, ${o}px, 0) scale(${s})`, lastScrollTop = e <= 0 ? 0 : e;
});
const menuBtn = document.querySelector(".menu-button"), megaMenu = document.querySelector(".mega-menu");
menuBtn.addEventListener("click", function() {
  megaMenu.classList.toggle("active");
});
function toggleMenu() {
  document.querySelector(".menu-button").classList.toggle("open");
}
document.addEventListener("DOMContentLoaded", function() {
  const e = document.querySelectorAll(".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent"), t = new IntersectionObserver((o, s) => {
    o.forEach((n) => {
      if (n.isIntersecting) {
        const r = Array.from(e).indexOf(n.target);
        setTimeout(() => {
          n.target.classList.add("in-view");
        }, r * 150), s.unobserve(n.target);
      }
    });
  }, { threshold: 0.2 });
  e.forEach((o) => t.observe(o));
}), document.addEventListener("DOMContentLoaded", () => {
  const e = document.getElementById("blogs-container"), t = document.getElementById("load-more-btn");
  if (!t) return;
  let o = parseInt(t.dataset.page) || 1;
  const s = e.dataset.url;
  let n = document.getElementById("load-less-btn");
  t.addEventListener("click", async () => {
    o++;
    try {
      const i = await (await fetch(`${s}?page=${o}`, { headers: { "X-Requested-With": "XMLHttpRequest" } })).text(), l = document.createElement("div");
      l.innerHTML = i, l.querySelectorAll(".blogs-card-parent").forEach((d) => e.appendChild(d)), l.querySelector("#hasMore") || (t.style.display = "none"), o > 1 && r();
    } catch (i) {
      console.error(i);
    }
  });
  function r() {
    if (n) {
      n.style.display = "flex";
      return;
    }
    n = document.createElement("button"), n.id = "load-less-btn", n.textContent = "LOAD LESS", n.className = "btn btn-secondary mt-3", e.parentNode.appendChild(n), n.addEventListener("click", c);
  }
  async function c() {
    try {
      o = 1;
      const i = await (await fetch(`${s}?page=1`, { headers: { "X-Requested-With": "XMLHttpRequest" } })).text(), l = document.createElement("div");
      l.innerHTML = i;
      const d = l.querySelectorAll(".blogs-card-parent");
      e.innerHTML = "", d.forEach((m) => e.appendChild(m)), t.style.display = "inline-block", n && (n.style.display = "none");
    } catch (i) {
      console.error(i);
    }
  }
}), document.addEventListener("DOMContentLoaded", function() {
  const e = document.querySelectorAll(".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent"), t = new IntersectionObserver((o, s) => {
    o.forEach((n) => {
      if (n.isIntersecting) {
        const r = Array.from(e).indexOf(n.target);
        setTimeout(() => {
          n.target.classList.add("in-view");
        }, r * 150), s.unobserve(n.target);
      }
    });
  }, { threshold: 0.2 });
  e.forEach((o) => t.observe(o));
}), document.addEventListener("DOMContentLoaded", () => {
  const e = [{ image: "asset/media/olivia-smith.webp", first: "Olivia", last: "Smith", location: "United States", testiDescription: "IdeoStream has become my daily morning read. The variety of topics covered is honestly impressive \u2014 from science to culture to personal development. It never feels like clickbait, just real, thoughtful writing." }, { image: "asset/media/harry-wilson.webp", first: "Harry", last: "Wilson", location: "Germany", testiDescription: "I stumbled across IdeoStream while searching for an article on productivity, and I've been hooked ever since. The content is well-researched and the writers clearly know what they're talking about. Bookmarked for life." }, { image: "asset/media/james-smith.webp", first: "James", last: "Smith", location: "UAE", testiDescription: "As someone who reads a lot of blogs for research, IdeoStream stands out. The articles are balanced and don't just skim the surface. I especially love the topics on psychology and human behavior. Would love even more academic references though!" }, { image: "asset/media/jeffrey-gordon.webp", first: "Jeffrey", last: "Gordon", location: "United Kingdom", testiDescription: "Running a business means I need to stay informed on a LOT of different things. IdeoStream literally covers everything \u2014 tech, business, lifestyle, world events. It's like having one smart friend who knows about everything." }, { image: "asset/media/freya-brown.webp", first: "Freya", last: "Brown", location: "China", testiDescription: "The writing quality on IdeoStream is what got me. Clean, engaging, no fluff. You can tell the team genuinely cares about the content they publish. I've even shared several articles with my own readers." }, { image: "asset/media/oliver-taylor.webp", first: "Oliver", last: "Taylor", location: "Canada", testiDescription: "I'm not the youngest person on the internet, but IdeoStream is easy to navigate and the articles are written in a way that's accessible without being dumbed down. Very refreshing compared to most news sites today." }, { image: "asset/media/alex-friedman.webp", first: "Alex", last: "Friedman", location: "United Kingdom", testiDescription: "I used an IdeoStream article as a starting point for one of my essays and it led me down the best rabbit hole. The content sparks curiosity in a way most blogs just don't. Highly recommend to anyone who loves learning." }, { image: "asset/media/lily-taylor.webp", first: "Lily", last: "Taylor", location: "Poland", testiDescription: "I finally found a blog that covers topics I actually care about \u2014 health, parenting, world news, lifestyle \u2014 all in one place. IdeoStream feels personal, not robotic. My only wish is that they posted even more frequently!" }, { image: "asset/media/thomas-williams.webp", first: "Thomas", last: "Williams", location: "Philippines", testiDescription: "From a professional standpoint, IdeoStream does something rare: it covers broad topics without losing depth. The editorial standards are clearly high. This is the kind of blog the internet actually needs more of." }, { image: "asset/media/amelia-williams.webp", first: "Amelia", last: "Williams", location: "Canada", testiDescription: "I travel constantly and IdeoStream keeps me connected to ideas and conversations happening around the world. Whether I'm reading about culture, technology, or health \u2014 the content always feels relevant and timely. A gem of a website." }];
  let t = 0;
  const o = document.querySelector(".testimonial-background-image"), s = document.querySelector(".first-name"), n = document.querySelector(".last-name"), r = document.querySelector(".location-title"), c = document.querySelector(".testimonial-description .description"), i = [s, n, r, c], l = document.querySelector(".arrow-right"), d = document.querySelector(".arrow-left");
  if (!o || i.some((a) => !a) || !l || !d) return;
  o.style.transition = "transform 1.2s cubic-bezier(.4,0,.2,1)", i.forEach((a) => {
    a.style.transition = "all .7s cubic-bezier(.4,0,.2,1)";
  });
  function m() {
    o.style.transform = "scale(1.2)", i.forEach((a) => {
      a.style.opacity = "0", a.style.transform = "translate3d(0,100%,0) scale3d(0,0,0) rotate(5deg)";
    });
  }
  function h() {
    o.style.transform = "scale(1)", i.forEach((a) => {
      a.style.opacity = "1", a.style.transform = "translate3d(0,0,0) scale3d(1,1,1) rotate(0)";
    });
  }
  function u() {
    m(), setTimeout(() => {
      const a = e[t];
      o.style.backgroundImage = `url("${a.image}")`, s.textContent = a.first, n.textContent = a.last, r.textContent = a.location, c.textContent = a.testiDescription, h();
    }, 500);
  }
  l.addEventListener("click", () => {
    t = (t + 1) % e.length, u();
  }), d.addEventListener("click", () => {
    t = (t - 1 + e.length) % e.length, u();
  }), u();
});
const menuItems = document.querySelectorAll(".news-letter .menu-item");
menuItems.forEach((e) => {
  const t = e.querySelector(".radio-btn-ctr"), o = e.querySelector(".radio-input");
  e.addEventListener("click", () => {
    document.querySelectorAll(".news-letter .radio-btn-ctr").forEach((s) => s.classList.remove("radio-checked")), document.querySelectorAll(".news-letter .radio-input").forEach((s) => s.checked = false), t.classList.add("radio-checked"), o.checked = true;
  });
});
const newsletterBg = document.querySelector(".background-newsletter");
let lastScroll = window.scrollY, current = 0, target = 0;
const speed = 0.08, scale = 1.55;
window.addEventListener("scroll", () => {
  const e = window.scrollY, t = e - lastScroll;
  target += t * 0.4, lastScroll = e;
});
function animate() {
  current += (target - current) * speed, !(!newsletterBg || window.matchMedia("(prefers-reduced-motion:reduce)").matches) && (newsletterBg.style.transform = `translate3d(0, ${-current}px, 0) scale3d(${scale}, ${scale}, 1)`, requestAnimationFrame(animate));
}
animate();
const scrollingItem = document.querySelector(".scrolling-item");
if (scrollingItem) {
  let e = function() {
    s += (o - s) * 0.08, scrollingItem.style.transform = `translate3d(${s}px, 0, 0)`, requestAnimationFrame(e);
  };
  var animateScrollingItem = e;
  let t = window.scrollY, o = 0, s = 0;
  window.addEventListener("scroll", () => {
    const n = window.scrollY, r = n - t;
    o -= r * 0.5, t = n;
  }), e();
}
document.addEventListener("DOMContentLoaded", function() {
  const e = document.querySelector(".blog-form-submission"), t = document.querySelector("input[name='fullName']"), o = document.querySelector("input[name='subject']"), s = document.querySelector(".email-field"), n = document.querySelector(".phone-field");
  if (!n) {
    console.log("Phone input not found");
    return;
  }
  const r = new IntersectionObserver((c) => {
    c.some((i) => i.isIntersecting) && (r.disconnect(), window.intlTelInput && !n.dataset.phoneReady && (n.dataset.phoneReady = "1", window.intlTelInput(n, { initialCountry: "pk", separateDialCode: true, utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js" })));
  }, { rootMargin: "200px" });
  r.observe(n);
}), document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".blog-form-submission").forEach(function(e) {
    e.addEventListener("submit", function() {
      const t = e.querySelector('[type="submit"]');
      t && (t.disabled = true, t.value = "Submitting...");
    });
  });
});
