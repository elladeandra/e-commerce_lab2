-- Database Setup Script for ecommerce_2025A_emmanuella_oteng
-- Run this script in phpMyAdmin or MySQL command line

USE ecommerce_2025A_emmanuella_oteng;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    cat_id INT AUTO_INCREMENT PRIMARY KEY,
    cat_name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create brands table
CREATE TABLE IF NOT EXISTS brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    user_email VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    user_role TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_cat INT,
    product_brand INT,
    product_title VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    product_desc TEXT,
    product_image VARCHAR(500),
    product_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_cat) REFERENCES categories(cat_id) ON DELETE SET NULL,
    FOREIGN KEY (product_brand) REFERENCES brands(brand_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample categories
INSERT IGNORE INTO categories (cat_name) VALUES 
('Electronics'),
('Clothing'),
('Books'),
('Home & Garden'),
('Sports'),
('Toys');

-- Insert sample brands
INSERT IGNORE INTO brands (brand_name) VALUES 
('Apple'),
('Nike'),
('Samsung'),
('Sony'),
('Adidas'),
('Canon');

-- Insert admin user (password: admin123)
INSERT IGNORE INTO users (user_name, user_email, user_password, user_role) 
VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
