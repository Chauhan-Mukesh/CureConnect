# CureConnect Medical Tourism Portal

A comprehensive medical tourism platform built with Symfony components and Twig templating, connecting international patients with India's world-class healthcare providers.

## Project Overview

CureConnect is a modern medical tourism portal with the following features:

- **Medical Tourism Focus**: Specialized platform for healthcare in India
- **Twig Templating**: Professional templating system with component separation
- **Interactive Features**: Dark/light mode toggle, smooth animations, enhanced UX
- **Content Management**: Treatment information, hospital gallery, government schemes, and contact forms
- **SEO Optimized**: Structured data, meta tags, and clean URLs for medical tourism keywords

## Features

### 🏥 Medical Tourism Features
- Treatment cost comparisons between India and other countries
- Hospital and facility galleries with detailed information
- Government schemes and e-Medical visa information
- Statistical dashboards for medical tourism data
- Consultation request forms and contact management

### 🎨 Design & UX
- Responsive Bootstrap 5 design
- Dark/light theme switching with smooth transitions
- Smooth scroll animations and hover effects
- Mobile-first responsive design
- Medical-themed color scheme and branding

### 📝 Content Features
- Treatment information pages with cost breakdowns
- Medical facility gallery with categorization
- Government schemes and visa process guides
- Contact forms with CSRF protection
- Article/blog system for medical tourism news

### 🛠 Technical Features
- **Backend**: Symfony Components (HTTP Foundation, Routing, Twig) with PHP 8.0+
- **Frontend**: Bootstrap 5, Enhanced CSS/JS with theme support
- **Database**: SQLite for development, MySQL for production
- **Security**: CSRF protection, input validation, and secure routing
- **Performance**: Optimized assets, lazy loading, and caching support
- **Code Quality**: PSR-12 standards and organized MVC structure

## Installation

### Prerequisites
- PHP 8.0+ with extensions: PDO, JSON, MBString
- Composer (optional - fallback autoloader included)
- Web server (Apache/Nginx) with mod_rewrite

### Setup Steps

1. **Clone/Download** the project
   ```bash
   git clone <repository-url>
   cd CureConnect
   ```

2. **Install Dependencies** (Optional - fallback autoloader available)
   ```bash
   # If composer is available
   composer install --no-dev
   
   # OR use the included simple autoloader (no external dependencies required)
   # The application will automatically fall back to the simple autoloader
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration:
   # - Set APP_ENV=development for development
   # - Configure database settings (SQLite by default)
   # - Set security keys for production
   ```

4. **Set up Database** (SQLite - automatic)
   ```bash
   # The application will automatically create the SQLite database
   # Database file: database/database.sqlite
   
   # For MySQL (production), update database.yaml and .env
   ```

5. **Start Development Server**
   ```bash
   composer dev-server
   # or
   php -S localhost:8000 -t public/
   ```

6. **Verify Installation**
   ```bash
   # Run tests to verify everything is working
   php tests/TemplateTest.php
   
   # Compare templates (if static files exist)
   php scripts/compare-templates.php
   ```

## Available Routes

The medical tourism portal includes the following routes:

| Route | Controller | Purpose |
|-------|------------|---------|
| `/` | HomeController::index | Homepage with treatment overview and statistics |
| `/about` | PageController::about | About CureConnect and our mission |
| `/contact` | PageController::contact | Contact form and consultation requests |
| `/gallery` | PageController::gallery | Medical facilities and hospital galleries |
| `/government-schemes` | PageController::governmentSchemes | Government initiatives and e-Medical visa info |
| `/article/{slug}` | PageController::article | Medical tourism articles and news |

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