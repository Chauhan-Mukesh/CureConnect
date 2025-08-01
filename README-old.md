# CureConnect Medical Tourism Portal

A comprehensive medical tourism platform for India, connecting international patients with world-class healthcare providers.

## Project Overview

CureConnect is a professional medical tourism portal built with vanilla PHP that helps international patients discover affordable, high-quality medical treatments in India. The platform includes:

- **Market Focus**: Indian medical tourism market valued at USD 8.19 billion (2024)
- **Target Audience**: International patients from 156+ countries with medical visa eligibility
- **Cost Advantage**: 30-70% savings compared to Western countries

## Features

### 🏥 Core Functionality
- Hospital listings with accreditation details
- Doctor profiles and specializations
- Treatment information with cost comparisons
- Medical visa guidance
- Multi-language support (English, Bengali, Arabic)

### 🎨 Technical Features
- Responsive Bootstrap 5 design
- SEO-optimized with structured data
- CSRF protection and security headers
- Database-driven content management
- Professional animations and interactions

### 📱 User Experience
- Smooth scroll animations
- Interactive search functionality
- Mobile-first responsive design
- Accessibility compliance
- Fast loading with optimized assets

## Installation

### Prerequisites
- PHP 8.0+
- MySQL 8.x
- Apache/Nginx with mod_rewrite
- Composer

### Setup Steps

1. **Clone/Download** the project to your web server directory
   ```
   D:\xampp\htdocs\CureConnect (current location)
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   - Copy `.env.example` to `.env` (if needed)
   - Update database credentials in `.env`
   - Set up other API keys as needed

4. **Database Setup**
   - Import `database/schema.sql` into MySQL
   - Default admin: admin@cureconnect.in / password

5. **Web Server Configuration**
   - Ensure mod_rewrite is enabled
   - Point document root to project folder
   - The `.htaccess` file handles URL rewriting

## Project Structure

```
/CureConnect
├── assets/              # CSS, JS, Images
│   ├── css/            # Theme, Components, Animations
│   ├── js/             # Core functionality
│   └── images/         # Static images
├── includes/           # Core PHP functions
│   ├── functions.php   # Utility functions
│   ├── database.php    # Database operations
│   └── auth.php        # Authentication system
├── templates/          # HTML templates
│   ├── shared/         # Header, Footer, Head
│   └── pages/          # Page-specific templates
├── lang/              # Internationalization
├── database/          # Schema and migrations
├── admin/             # Admin dashboard
├── articles/          # SEO-friendly articles
└── public/            # Public assets
```

## Technology Stack

- **Backend**: Vanilla PHP 8.x with PDO
- **Frontend**: Bootstrap 5, Vanilla JavaScript
- **Database**: MySQL 8.x with utf8mb4
- **Security**: CSRF protection, prepared statements
- **SEO**: Structured data, meta tags, clean URLs

## Current Status

✅ **Completed Setup:**
- Folder structure created
- Core PHP files (config, functions, database, auth)
- Professional CSS with animations
- JavaScript functionality (search, forms, animations)
- Database schema with all necessary tables
- Main homepage with hero section and features
- Language support framework
- SEO-friendly URL structure
- Security configurations

## Next Steps

To make the project fully operational:

1. **Create additional pages**: About, Contact, Treatments listing
2. **Set up database**: Import the schema and add sample data
3. **Configure web server**: Set up virtual host if needed
4. **Add content**: Upload hospital/doctor/treatment data
5. **Test functionality**: Forms, search, navigation

## Access the Project

Once XAMPP is running, access the project at:
- Local: `http://localhost/CureConnect`
- With virtual host: `http://cureconnect.local` (if configured)

The project is designed to be production-ready with proper security, SEO optimization, and professional UI/UX for the medical tourism industry.
