/**
 * Blog Theme JavaScript - Extracted from HTML templates
 * Custom scripts for blog functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // --- Initialize AOS (Animate on Scroll) if available ---
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 50,
        });
    }

    // --- Navbar Scroll Effect ---
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

    // --- Theme Toggler (Dark/Light Mode) ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        const themeToggleIcon = themeToggleBtn.querySelector('.theme-toggle-icon');
        const htmlEl = document.documentElement;
        const moonIcon = 'bi-moon-stars-fill';
        const sunIcon = 'bi-brightness-high-fill';

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
            if (themeToggleIcon) {
                themeToggleIcon.classList.add('animating');
            }
            
            if (!document.startViewTransition) {
                const newTheme = toggleTheme();
                updateIcon(newTheme);
                return;
            }

            const x = e.clientX;
            const y = e.clientY;
            const endRadius = Math.hypot(
                Math.max(x, innerWidth - x),
                Math.max(y, innerHeight - y)
            );

            const transition = document.startViewTransition(() => {
                const newTheme = toggleTheme();
                updateIcon(newTheme);
            });

            transition.ready.then(() => {
                const clipPath = [
                    `circle(0px at ${x}px ${y}px)`,
                    `circle(${endRadius}px at ${x}px ${y}px)`,
                ];
                document.documentElement.animate(
                    {
                        clipPath: htmlEl.getAttribute('data-bs-theme') === 'dark' ? [...clipPath].reverse() : clipPath,
                    },
                    {
                        duration: 500,
                        easing: 'cubic-bezier(0.65, 0, 0.35, 1)',
                        pseudoElement: htmlEl.getAttribute('data-bs-theme') === 'dark' ? '::view-transition-old(root)' : '::view-transition-new(root)',
                    }
                );
            });
        });
        
        // Initialize theme from localStorage or system preference
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        htmlEl.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);
    }
    
    // --- Smoother "Back to Top" Logic ---
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- Statistics Counter Animation ---
    const statsCounters = document.querySelectorAll('.stats-counter');
    if (statsCounters.length > 0) {
        const animateCounters = () => {
            statsCounters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const count = parseInt(counter.innerText);
                const increment = target / 100;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(animateCounters, 50);
                } else {
                    counter.innerText = target;
                }
            });
        };

        // Start animation when counters come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        statsCounters.forEach(counter => {
            observer.observe(counter);
        });
    }

    // --- Smooth Scroll for Anchor Links ---
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

    // --- Form Validation Enhancement ---
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // --- Gallery Lightbox Effect (if gallery exists) ---
    const galleryItems = document.querySelectorAll('.gallery-item');
    if (galleryItems.length > 0) {
        galleryItems.forEach(item => {
            item.addEventListener('click', () => {
                const img = item.querySelector('img');
                if (img) {
                    // Simple lightbox implementation
                    const lightbox = document.createElement('div');
                    lightbox.className = 'lightbox';
                    lightbox.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.8);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                        cursor: pointer;
                    `;
                    
                    const lightboxImg = document.createElement('img');
                    lightboxImg.src = img.src;
                    lightboxImg.style.cssText = `
                        max-width: 90%;
                        max-height: 90%;
                        object-fit: contain;
                        border-radius: 8px;
                    `;
                    
                    lightbox.appendChild(lightboxImg);
                    document.body.appendChild(lightbox);
                    
                    lightbox.addEventListener('click', () => {
                        document.body.removeChild(lightbox);
                    });
                }
            });
        });
    }

    // --- Contact Form AJAX Submission (placeholder) ---
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            
            try {
                // Replace with actual endpoint
                const response = await fetch('/contact', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success';
                    successAlert.textContent = 'Thank you for your message! We\'ll get back to you soon.';
                    contactForm.insertBefore(successAlert, contactForm.firstChild);
                    
                    // Reset form
                    contactForm.reset();
                    contactForm.classList.remove('was-validated');
                    
                    // Remove success message after 5 seconds
                    setTimeout(() => {
                        successAlert.remove();
                    }, 5000);
                } else {
                    throw new Error('Network response was not ok');
                }
            } catch (error) {
                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger';
                errorAlert.textContent = 'Sorry, there was an error sending your message. Please try again.';
                contactForm.insertBefore(errorAlert, contactForm.firstChild);
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    errorAlert.remove();
                }, 5000);
            } finally {
                // Reset button state
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }

    // --- Search Functionality (if search form exists) ---
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    if (searchForm && searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = searchInput.value.trim();
                if (query.length > 2) {
                    // Implement search functionality
                    console.log('Searching for:', query);
                }
            }, 300);
        });
    }

    // --- Newsletter Subscription (if exists) ---
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = newsletterForm.querySelector('input[type="email"]').value;
            const submitBtn = newsletterForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            
            submitBtn.textContent = 'Subscribing...';
            submitBtn.disabled = true;
            
            try {
                // Replace with actual endpoint
                const response = await fetch('/newsletter/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ email })
                });
                
                if (response.ok) {
                    submitBtn.textContent = 'Subscribed!';
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-success');
                    newsletterForm.querySelector('input[type="email"]').value = '';
                } else {
                    throw new Error('Subscription failed');
                }
            } catch (error) {
                submitBtn.textContent = 'Error';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-danger');
                
                setTimeout(() => {
                    submitBtn.textContent = originalBtnText;
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.classList.add('btn-primary');
                }, 3000);
            } finally {
                submitBtn.disabled = false;
            }
        });
    }
});

// --- Utility Functions ---

// Debounce function for performance optimization
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// --- Article Page Specific Functions ---
function initializeArticleFeatures() {
    // Reading Progress Bar
    const progressBar = document.getElementById('reading-progress');
    const article = document.querySelector('.article-body');
    
    if (progressBar && article) {
        window.addEventListener('scroll', function() {
            const articleTop = article.offsetTop - 100;
            const articleHeight = article.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset;
            
            if (scrollTop >= articleTop) {
                const progress = Math.min(
                    ((scrollTop - articleTop) / (articleHeight - windowHeight)) * 100,
                    100
                );
                progressBar.style.width = Math.max(progress, 0) + '%';
            } else {
                progressBar.style.width = '0%';
            }
        });
    }
    
    // Smooth scroll for table of contents links
    document.querySelectorAll('.table-of-contents a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
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

// Initialize article features if we're on an article page
if (document.querySelector('.article-body')) {
    initializeArticleFeatures();
}

// --- Gallery Page Specific Functions ---
function initializeGalleryFeatures() {
    // Load More functionality
    const loadMoreBtn = document.getElementById('load-more-btn');
    const hiddenItems = document.querySelectorAll('.gallery-hidden');
    
    if (loadMoreBtn && hiddenItems.length > 0) {
        loadMoreBtn.addEventListener('click', function() {
            hiddenItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.remove('d-none');
                    // Re-initialize AOS for newly shown items
                    if (typeof AOS !== 'undefined') {
                        AOS.refresh();
                    }
                }, index * 100);
            });
            
            loadMoreBtn.style.display = 'none';
        });
    }
    
    // Lightbox functionality
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxClose = document.getElementById('lightbox-close');
    const galleryItems = document.querySelectorAll('[data-lightbox]');
    
    galleryItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const imageSrc = this.getAttribute('data-lightbox');
            const imageTitle = this.getAttribute('data-title');
            
            lightboxImage.src = imageSrc;
            lightboxImage.alt = imageTitle;
            lightbox.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close lightbox
    function closeLightbox() {
        lightbox.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
    }
    
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }
    
    // Close lightbox with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox && lightbox.classList.contains('show')) {
            closeLightbox();
        }
    });
}

// Initialize gallery features if we're on a gallery page
if (document.querySelector('.gallery-grid')) {
    initializeGalleryFeatures();
}

// --- Home Page Specific Functions ---
function initializeHomePage() {
    // Smooth scroll for anchor links
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

// Initialize home page features if we're on home page
if (document.querySelector('.hero-section')) {
    initializeHomePage();
}

// Export functions for potential use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { 
        debounce, 
        throttle, 
        initializeArticleFeatures, 
        initializeGalleryFeatures, 
        initializeHomePage 
    };
}