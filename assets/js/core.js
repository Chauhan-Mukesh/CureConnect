/**
 * CureConnect Medical Tourism Portal - Core JavaScript
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
        this.bindEvents();
    }

    // CSRF Token Setup
    setupCSRF() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            // Set up CSRF token for all AJAX requests
            if (typeof $ !== 'undefined' && $.ajaxSetup) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
            }

            // For vanilla JS fetch requests
            window.csrfToken = token;
        }
    }

    // Scroll Animations
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements with animation classes
        document.querySelectorAll('.animate-on-scroll, .stagger-animation').forEach(el => {
            observer.observe(el);
        });
    }

    // Back to Top Button
    setupBackToTop() {
        const backToTopBtn = document.getElementById('backToTop');
        if (!backToTopBtn) return;

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'block';
                backToTopBtn.classList.add('fade-in');
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

        // Real-time validation
        const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
    }

    validateField(field) {
        const isValid = field.checkValidity();
        field.classList.remove('is-valid', 'is-invalid');
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');

        // Show/hide feedback
        const feedback = field.parentNode.querySelector('.invalid-feedback, .valid-feedback');
        if (feedback) {
            feedback.style.display = isValid ? 'none' : 'block';
        }
    }

    // Language Switcher
    setupLanguageSwitcher() {
        const langLinks = document.querySelectorAll('a[href*="?lang="]');
        langLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const url = new URL(link.href);
                const lang = url.searchParams.get('lang');

                // Store language preference
                localStorage.setItem('preferred_language', lang);

                // Show loading state
                this.showLoading();
            });
        });
    }

    // Search Functionality
    setupSearchFunctionality() {
        const searchInputs = document.querySelectorAll('.search-input');
        searchInputs.forEach(input => {
            let searchTimeout;

            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 500);
            });
        });
    }

    async performSearch(query) {
        if (query.length < 3) return;

        try {
            const response = await fetch('/api/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ query })
            });

            const results = await response.json();
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    displaySearchResults(results) {
        const resultsContainer = document.getElementById('search-results');
        if (!resultsContainer) return;

        if (results.length === 0) {
            resultsContainer.innerHTML = '<p class="text-muted">No results found</p>';
            return;
        }

        const html = results.map(result => `
            <div class="search-result-item p-3 border-bottom">
                <h6><a href="${result.url}">${result.title}</a></h6>
                <p class="text-muted small">${result.excerpt}</p>
            </div>
        `).join('');

        resultsContainer.innerHTML = html;
    }

    // Counter Animation
    setupCounters() {
        const counters = document.querySelectorAll('.stats-counter');
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
        const target = parseInt(element.dataset.count || element.textContent);
        const duration = 2000;
        const start = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);

            const current = Math.floor(progress * target);
            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    // Lazy Loading
    setupLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('skeleton');
                    img.classList.add('fade-in');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Event Bindings
    bindEvents() {
        // Handle AJAX forms
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });

        // Handle modal triggers
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-bs-toggle="modal"]')) {
                const targetModal = document.querySelector(e.target.dataset.bsTarget);
                if (targetModal && e.target.dataset.loadContent) {
                    this.loadModalContent(targetModal, e.target.dataset.loadContent);
                }
            }
        });

        // Handle tooltips
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    // AJAX Form Handler
    async handleAjaxForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');

        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner me-2"></span>Processing...';
        }

        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', result.message);
                if (result.redirect) {
                    setTimeout(() => window.location.href = result.redirect, 1500);
                }
            } else {
                this.showAlert('danger', result.message);
            }
        } catch (error) {
            this.showAlert('danger', 'An error occurred. Please try again.');
        } finally {
            // Reset button state
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.dataset.originalText || 'Submit';
            }
        }
    }

    // Load Modal Content
    async loadModalContent(modal, url) {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = '<div class="text-center"><div class="spinner"></div></div>';

        try {
            const response = await fetch(url);
            const content = await response.text();
            modalBody.innerHTML = content;
        } catch (error) {
            modalBody.innerHTML = '<div class="alert alert-danger">Failed to load content</div>';
        }
    }

    // Utility Methods
    showAlert(type, message) {
        const alertsContainer = document.getElementById('alerts-container') || document.body;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        alertsContainer.insertBefore(alert, alertsContainer.firstChild);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    showLoading() {
        if (document.getElementById('loading-overlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    }

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.cureConnect = new CureConnect();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CureConnect;
}
