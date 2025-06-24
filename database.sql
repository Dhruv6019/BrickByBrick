-- Database creation
CREATE DATABASE IF NOT EXISTS newreal;
USE newreal;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('user', 'admin', 'developer') DEFAULT 'user',
    company_name VARCHAR(100),
    license_number VARCHAR(50),
    years_experience INT,
    email_verified BOOLEAN DEFAULT FALSE,
    verification_code VARCHAR(6),
    verification_expiry DATETIME,
    reset_token VARCHAR(255),
    reset_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    property_type ENUM('house', 'apartment', 'land', 'commercial') NOT NULL,
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10,2),
    seller_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Property images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id)
);

-- Developer contacts table
CREATE TABLE IF NOT EXISTS developer_contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for optimization
CREATE INDEX idx_property_status ON properties(status);
CREATE INDEX idx_property_type ON properties(property_type);
CREATE INDEX idx_property_location ON properties(location);
CREATE INDEX idx_property_price ON properties(price);
CREATE INDEX idx_user_type ON users(user_type);

-- Property amenities table
CREATE TABLE IF NOT EXISTS property_amenities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    amenity_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Property reviews table
CREATE TABLE IF NOT EXISTS property_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Property comparisons table
CREATE TABLE IF NOT EXISTS property_comparisons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    comparison_group VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Visit enquiries table
CREATE TABLE IF NOT EXISTS visit_enquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    user_id INT NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME NOT NULL,
    message TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Developer projects table
CREATE TABLE IF NOT EXISTS developer_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    developer_id INT NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255) NOT NULL,
    status ENUM('planning', 'ongoing', 'completed') DEFAULT 'planning',
    completion_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (developer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Additional indexes for optimization
CREATE INDEX idx_property_amenities ON property_amenities(property_id);
CREATE INDEX idx_property_reviews ON property_reviews(property_id);
CREATE INDEX idx_visit_enquiries_status ON visit_enquiries(status);
CREATE INDEX idx_developer_projects_status ON developer_projects(status);
CREATE INDEX idx_comparison_group ON property_comparisons(comparison_group);

-- Sample admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, user_type, email_verified) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin@example.com', 'Admin User', 'admin', 1);
