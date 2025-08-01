# CureConnect Blog Platform

A comprehensive blog platform built with vanilla PHP and Twig templating, featuring modern design and responsive layouts.

## Project Overview

CureConnect has been transformed into a modern blog platform with the following features:

- **Modern Blog Design**: Clean, responsive layout optimized for content
- **Twig Templating**: Professional templating system with component separation
- **Interactive Features**: Dark/light mode toggle, reading progress, animations
- **Content Management**: Article pages, gallery, categories, and contact forms
- **SEO Optimized**: Structured data, meta tags, and clean URLs

## Features

### 🎨 Design & UX
- Responsive Bootstrap 5 design
- Dark/light theme switching with smooth transitions
- Smooth scroll animations (AOS library)
- Interactive hover effects and animations
- Mobile-first responsive design
- Reading progress indicator

### 📝 Content Features
- Article templates with rich content support
- Image gallery with lightbox functionality
- Category browsing and organization
- Contact forms with validation
- About page with team information
- Newsletter subscription

### 🛠 Technical Features
- **Backend**: Vanilla PHP 8.0+ with Twig templating
- **Frontend**: Bootstrap 5, Custom CSS/JS
- **Security**: CSRF protection and input validation
- **Performance**: Optimized assets and lazy loading
- **Code Quality**: PSR-12 standards and organized structure

## Installation

### Prerequisites
- PHP 8.0+
- Composer
- Web server (Apache/Nginx) with mod_rewrite

### Setup Steps

1. **Clone/Download** the project
   ```bash
   git clone <repository-url>
   cd CureConnect
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Start Development Server**
   ```bash
   composer dev-server
   # or
   php -S localhost:8000 -t public/
   ```

## Project Structure

```
./CureConnect
├── assets/              # Static assets
│   ├── css/            # Stylesheets
│   │   ├── theme.css   # Main theme styles
│   │   ├── components.css
│   │   ├── animations.css
│   │   └── blog-theme.css  # Blog-specific styles
│   ├── js/             # JavaScript files
│   │   ├── core.js     # Core functionality
│   │   └── blog-theme.js   # Blog-specific scripts
│   └── images/         # Static images
├── src/                # PHP application code
│   ├── Core/          # Core application classes
│   ├── Controller/    # Page controllers
│   ├── Models/        # Data models
│   └── Services/      # Business logic services
├── templates/          # Twig templates
│   ├── base.html.twig # Base template
│   ├── pages/         # Page templates
│   │   ├── home.html.twig
│   │   ├── about.html.twig
│   │   ├── contact.html.twig
│   │   ├── gallery.html.twig
│   │   ├── article.html.twig
│   │   └── government-schemes.html.twig
│   └── shared/        # Shared components
│       ├── header.html.twig
│       └── footer.html.twig
├── tests/             # Test files
├── lang/              # Internationalization
├── config/            # Configuration files
└── public/            # Web server document root
```

## Technology Stack

- **Backend**: Vanilla PHP 8.x with Twig templating
- **Frontend**: Bootstrap 5, Modern CSS, Vanilla JavaScript
- **Dependencies**: Managed with Composer
- **Testing**: Custom template tests
- **Code Quality**: PSR-12 compliant

## Current Status

✅ **Completed Features:**
- ✅ Complete Twig template system
- ✅ Responsive blog design with dark/light themes
- ✅ Interactive components and animations
- ✅ SEO-optimized structure
- ✅ Clean CSS/JS architecture
- ✅ Template testing framework
- ✅ Asset organization and optimization

✅ **Template Conversion:**
- ✅ Extracted all inline CSS to `blog-theme.css`
- ✅ Extracted all inline JavaScript to `blog-theme.js`
- ✅ Converted all static HTML to Twig templates
- ✅ Updated header and footer for blog functionality
- ✅ Removed static HTML files after verification

## Development

### Running Tests
```bash
# Run template tests
php tests/TemplateTest.php

# Run code quality checks (when dependencies installed)
composer quality
```

### Key Features

1. **Template System**: Professional Twig templating with inheritance
2. **Asset Management**: Organized CSS/JS with blog-specific extensions
3. **Interactive UI**: Theme switching, animations, and user feedback
4. **Content Structure**: Flexible template system for various content types
5. **Performance**: Optimized loading and responsive design

## Recent Updates

- ✅ Migrated from static HTML to dynamic Twig templates
- ✅ Extracted and organized all inline styles and scripts
- ✅ Implemented blog-focused navigation and features
- ✅ Added comprehensive template testing
- ✅ Optimized asset structure and performance
- ✅ Enhanced SEO and accessibility features

## Usage

The platform is ready for content management with:
- Article creation and management
- Gallery image organization
- Category-based content browsing
- Contact form handling
- Newsletter subscription
- Multi-language support framework

Visit `http://localhost:8000` to access the platform after setup.

---

**Note**: This platform has been successfully converted from a medical tourism site to a modern blog platform with clean, maintainable code and professional templating architecture.