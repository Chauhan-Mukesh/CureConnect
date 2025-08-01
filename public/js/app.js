/**
 * CureConnect Medical Tourism Portal - Consolidated Application JavaScript
 * Combined from core.js and blog-theme.js for Bootstrap 5.3.0 compatibility
 */

class CureConnect {
    constructor() {
        this.init();
    }

    init() {
        this.setupCSRF();
        this.setupScrollAnimations();
        this.setupBackToTop();
        this.setupFormValidation();
        this.setupLanguageSwitcher();
        this.setupSearchFunctionality();
        this.setupCounters();
        this.setupLazyLoading();
        this.setupThemeToggle();
        this.setupNavbarScrollEffect();
        this.bindEvents();
    }

    // CSRF Token Setup
    setupCSRF() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            // For vanilla JS fetch requests
            window.csrfToken = token;
        }
    }

    // Scroll Animations using Intersection Observer
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe elements with animation classes
        document.querySelectorAll('.fade-in-up, .animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    // Back to Top Button
    setupBackToTop() {
        const backToTopBtn = document.getElementById('back-to-top');
        if (backToTopBtn) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });

            backToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    // Form Validation
    setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', (event) => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // Language Switcher
    setupLanguageSwitcher() {
        const langSwitcher = document.getElementById('language-switcher');
        if (langSwitcher) {
            langSwitcher.addEventListener('change', (event) => {
                const selectedLang = event.target.value;
                window.location.href = `${window.location.pathname}?lang=${selectedLang}`;
            });
        }
    }

    // Search Functionality
    setupSearchFunctionality() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (event) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(event.target.value);
                }, 300);
            });
        }
    }

    performSearch(query) {
        if (query.length < 2) return;
        
        // Implement search logic here
        console.log('Searching for:', query);
    }

    // Animated Counters
    setupCounters() {
        const counters = document.querySelectorAll('.counter');
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        });

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    }

    animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;

        const timer = setInterval(() => {
            current += increment;
            element.textContent = Math.floor(current);
            
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            }
        }, 16);
    }

    // Lazy Loading for Images
    setupLazyLoading() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Theme Toggle (Dark/Light Mode)
    setupThemeToggle() {
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            const htmlEl = document.documentElement;
            
            // Load saved theme or default to light
            const savedTheme = localStorage.getItem('theme') || 'light';
            htmlEl.setAttribute('data-bs-theme', savedTheme);

            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = htmlEl.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                htmlEl.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }
    }

    // Navbar Scroll Effect
    setupNavbarScrollEffect() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }
    }

    // Bind Additional Events
    bindEvents() {
        // Bootstrap tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }

        // Bootstrap popovers
        if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new CureConnect();
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CureConnect;
}