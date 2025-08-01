/**
 * CureConnect Medical Tourism Portal - Consolidated Application JavaScript
 * Combined from app.js and HTML inline scripts for Bootstrap 5.3.0+ compatibility
 * Includes AOS (Animate on Scroll) integration and enhanced theme management
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
        this.setupAOSAnimations();
        this.bindEvents();
    }

    // CSRF Token Setup
    setupCSRF() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            // For vanilla JS fetch requests
            window.csrfToken = token;
            
            // For jQuery AJAX requests
            if (typeof $ !== 'undefined') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
            }
        }
    }

    // Initialize AOS (Animate on Scroll) Library
    setupAOSAnimations() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
                easing: 'ease-out-cubic'
            });
        }
    }

    // Enhanced Theme Toggle with Local Storage and Animation
    setupThemeToggle() {
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (!themeToggleBtn) return;

        const themeToggleIcon = themeToggleBtn.querySelector('.theme-toggle-icon');
        const htmlEl = document.documentElement;
        const moonIcon = 'bi-moon-stars-fill';
        const sunIcon = 'bi-brightness-high-fill';

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlEl.setAttribute('data-bs-theme', savedTheme);
        this.updateThemeIcon(themeToggleIcon, savedTheme, moonIcon, sunIcon);

        const updateIcon = (theme) => {
            if (themeToggleIcon) {
                themeToggleIcon.classList.remove('animating');
                if (theme === 'dark') {
                    themeToggleIcon.classList.remove(sunIcon);
                    themeToggleIcon.classList.add(moonIcon);
                } else {
                    themeToggleIcon.classList.remove(moonIcon);
                    themeToggleIcon.classList.add(sunIcon);
                }
            }
        };

        const toggleTheme = () => {
            const newTheme = htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            htmlEl.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            return newTheme;
        };

        themeToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            if (themeToggleIcon) {
                themeToggleIcon.classList.add('animating');
            }
            
            if (!document.startViewTransition) {
                const newTheme = toggleTheme();
                updateIcon(newTheme);
            } else {
                document.startViewTransition(() => {
                    const newTheme = toggleTheme();
                    updateIcon(newTheme);
                });
            }
        });
    }

    updateThemeIcon(icon, theme, moonIcon, sunIcon) {
        if (!icon) return;
        
        if (theme === 'dark') {
            icon.classList.remove(sunIcon);
            icon.classList.add(moonIcon);
        } else {
            icon.classList.remove(moonIcon);
            icon.classList.add(sunIcon);
        }
    }

    // Scroll Animations and Effects
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observe elements for scroll animations
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    // Back to Top Button
    setupBackToTop() {
        const backToTopBtn = document.createElement('button');
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
        backToTopBtn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(backToTopBtn);

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Enhanced Form Validation
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

        // Real-time validation feedback
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            });
        });
    }

    // Language Switcher
    setupLanguageSwitcher() {
        const langSwitcher = document.getElementById('language-switcher');
        if (langSwitcher) {
            langSwitcher.addEventListener('change', (e) => {
                const selectedLang = e.target.value;
                // Implementation would depend on your i18n system
                // Language switching functionality
                // Example: window.location.href = `/switch-language/${selectedLang}`;
                if (selectedLang) {
                    // Handle language switch
                }
            });
        }
    }

    // Search Functionality
    setupSearchFunctionality() {
        const searchInput = document.getElementById('search-input');
        const searchResults = document.getElementById('search-results');
        
        if (searchInput && searchResults) {
            let searchTimeout;
            
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length < 3) {
                    searchResults.innerHTML = '';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    this.performSearch(query, searchResults);
                }, 300);
            });
        }
    }

    async performSearch(query, resultsContainer) {
        try {
            const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken || ''
                }
            });
            
            if (response.ok) {
                const results = await response.json();
                this.displaySearchResults(results, resultsContainer);
            }
        } catch (error) {
            // Handle search error silently or show user-friendly message
            if (error instanceof Error) {
                // Log error for debugging in development
            }
        }
    }

    displaySearchResults(results, container) {
        if (results.length === 0) {
            container.innerHTML = '<div class="p-3 text-muted">No results found</div>';
            return;
        }
        
        const html = results.map(result => `
            <div class="search-result-item p-3 border-bottom">
                <h6><a href="${result.url}" class="text-decoration-none">${result.title}</a></h6>
                <p class="text-muted small mb-0">${result.excerpt}</p>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }

    // Counter Animation
    setupCounters() {
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16);
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };
            
            // Start animation when element comes into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe(counter);
        });
    }

    // Lazy Loading for Images
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Navbar Scroll Effect
    setupNavbarScrollEffect() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            let lastScrollY = window.scrollY;
            
            window.addEventListener('scroll', () => {
                const currentScrollY = window.scrollY;
                
                if (currentScrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                // Optional: Hide navbar on scroll down, show on scroll up
                if (currentScrollY > lastScrollY && currentScrollY > 100) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
                
                lastScrollY = currentScrollY;
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

        // Enhanced form interactions
        document.querySelectorAll('.btn-submit').forEach(button => {
            button.addEventListener('click', function() {
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                this.disabled = true;
            });
        });
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new CureConnect();
});

// Export for potential module usage (Node.js environment)
if (typeof window === 'undefined' && typeof module !== 'undefined' && module.exports) {
    module.exports = CureConnect;
}

// Global utility functions
window.CureConnect = {
    showToast: function(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        const toast = this.createToast(message, type);
        toastContainer.appendChild(toast);
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            new bootstrap.Toast(toast).show();
        }
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    },
    
    createToastContainer: function() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1080';
        document.body.appendChild(container);
        return container;
    },
    
    createToast: function(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        return toast;
    }
};