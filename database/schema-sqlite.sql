-- CureConnect SQLite3 Schema for Testing
-- Lightweight version of the main database schema optimized for SQLite3

PRAGMA foreign_keys = ON;

-- Users table
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    is_admin BOOLEAN DEFAULT 0,
    role TEXT CHECK(role IN ('user','admin','hospital_admin','doctor')) NOT NULL DEFAULT 'user',
    status TEXT CHECK(status IN ('active','inactive','suspended')) NOT NULL DEFAULT 'active',
    email_verified INTEGER DEFAULT 0,
    email_verification_token VARCHAR(255) DEFAULT NULL,
    password_reset_token VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_role ON users(role);

-- Articles table
CREATE TABLE articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT DEFAULT NULL,
    language VARCHAR(5) DEFAULT 'en',
    meta_description TEXT DEFAULT NULL,
    meta_keywords TEXT DEFAULT NULL,
    tags TEXT DEFAULT NULL, -- JSON stored as TEXT in SQLite
    category VARCHAR(100) DEFAULT NULL,
    author_name VARCHAR(100) DEFAULT NULL,
    featured_image VARCHAR(255) DEFAULT NULL,
    reading_time INTEGER DEFAULT NULL,
    views INTEGER DEFAULT 0,
    featured INTEGER DEFAULT 0,
    scheduled_at DATETIME DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    status TEXT CHECK(status IN ('draft','scheduled','published','archived')) DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_articles_slug ON articles(slug);
CREATE INDEX idx_articles_status_lang ON articles(status, language);
CREATE INDEX idx_articles_category ON articles(category);
CREATE INDEX idx_articles_published_at ON articles(published_at);

-- Countries table
CREATE TABLE countries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(3) NOT NULL UNIQUE,
    phone_code VARCHAR(10) DEFAULT NULL,
    medical_visa_eligible INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_countries_code ON countries(code);

-- Hospitals table
CREATE TABLE hospitals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    address TEXT,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    established_year INTEGER DEFAULT NULL,
    bed_count INTEGER DEFAULT NULL,
    accreditations TEXT DEFAULT NULL, -- JSON stored as TEXT
    specialties TEXT DEFAULT NULL, -- JSON stored as TEXT
    facilities TEXT DEFAULT NULL, -- JSON stored as TEXT
    images TEXT DEFAULT NULL, -- JSON stored as TEXT
    rating DECIMAL(2,1) DEFAULT 0.0,
    review_count INTEGER DEFAULT 0,
    featured INTEGER DEFAULT 0,
    status TEXT CHECK(status IN ('active','inactive')) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_hospitals_slug ON hospitals(slug);
CREATE INDEX idx_hospitals_city ON hospitals(city);
CREATE INDEX idx_hospitals_status ON hospitals(status);

-- Inquiries table
CREATE TABLE inquiries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    treatment_interest VARCHAR(255) DEFAULT NULL,
    hospital_preference VARCHAR(255) DEFAULT NULL,
    message TEXT,
    status TEXT CHECK(status IN ('new','contacted','qualified','converted','closed')) DEFAULT 'new',
    assigned_to INTEGER DEFAULT NULL,
    source VARCHAR(100) DEFAULT 'website',
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_inquiries_status ON inquiries(status);
CREATE INDEX idx_inquiries_treatment ON inquiries(treatment_interest);

-- Settings table
CREATE TABLE settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    type TEXT CHECK(type IN ('string','number','boolean','json')) DEFAULT 'string',
    description TEXT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_settings_key ON settings(key);

-- Insert test data
INSERT INTO users (email, password_hash, name, is_admin, role, status, email_verified) VALUES
('admin@cureconnect.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 1, 'admin', 'active', 1),
('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 0, 'user', 'active', 1);

INSERT INTO countries (name, code, phone_code, medical_visa_eligible) VALUES
('United States', 'USA', '+1', 1),
('United Kingdom', 'GBR', '+44', 1),
('Canada', 'CAN', '+1', 1),
('Australia', 'AUS', '+61', 1),
('India', 'IND', '+91', 1);

INSERT INTO articles (title, slug, content, excerpt, language, category, author_name, status, published_at) VALUES
('Guide to Medical Tourism in India', 'guide-medical-tourism-india',
'<p>India has emerged as a leading destination for medical tourism...</p>',
'Complete guide to medical tourism opportunities in India.',
'en', 'Medical Tourism', 'Dr. Rajesh Kumar', 'published', datetime('now')),
('Top Hospitals for International Patients', 'top-hospitals-international-patients',
'<p>Discover the best hospitals in India for international patients...</p>',
'Explore top-rated hospitals catering to international patients.',
'en', 'Hospitals', 'Dr. Priya Singh', 'published', datetime('now'));

INSERT INTO settings (key, value, type, description) VALUES
('site_name', 'CureConnect Medical Tourism', 'string', 'Website name'),
('site_tagline', 'World-Class Healthcare in India', 'string', 'Website tagline'),
('contact_email', 'info@cureconnect.in', 'string', 'Main contact email'),
('contact_phone', '+91-1800-123-4567', 'string', 'Main contact phone');