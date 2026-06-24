// public/js/landing.js
// This script handles animated counters and scroll-reveal effects for the landing page.
// It runs after DOMContentLoaded as per project conventions.

document.addEventListener("DOMContentLoaded", function () {
    // Counter animation using IntersectionObserver
    const counters = document.querySelectorAll('[data-counter]');
    const counterObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const targetText = el.textContent.trim();
                // Extract numeric part and any suffix (e.g., +, %)
                const match = targetText.match(/([\d,.]+)(.*)/);
                if (match) {
                    const start = 0;
                    const end = parseFloat(match[1].replace(/,/g, ""));
                    const suffix = match[2] || "";
                    const duration = 2000; // 2 seconds
                    let startTime = null;
                    const step = timestamp => {
                        if (!startTime) startTime = timestamp;
                        const progress = Math.min((timestamp - startTime) / duration, 1);
                        const value = Math.floor(progress * (end - start) + start);
                        el.textContent = value.toLocaleString() + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(step);
                        } else {
                            // Ensure final value matches original format
                            el.textContent = targetText;
                        }
                    };
                    requestAnimationFrame(step);
                }
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.6 });
    counters.forEach(counter => counterObserver.observe(counter));

    // Scroll-reveal for elements with data-scroll-reveal attribute
    const revealElements = document.querySelectorAll('[data-scroll-reveal]');
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    revealElements.forEach(el => revealObserver.observe(el));
});
