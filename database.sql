-- MyCling E-commerce Database

-- Create database
CREATE DATABASE IF NOT EXISTS mycling_db;
USE mycling_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id INT,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    category_id INT,
    image_url VARCHAR(255),
    stock_quantity INT NOT NULL DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    is_sale BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3, 1) DEFAULT 0,
    rating_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Cart items table
CREATE TABLE IF NOT EXISTS cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100),
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT,
    payment_method VARCHAR(50),
    shipping_fee DECIMAL(10, 2) DEFAULT 0,
    tax DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Hoodies', 'Comfortable hoodies for all seasons'),
('T-Shirts', 'Casual t-shirts for everyday wear'),
('Jeans', 'Stylish jeans for men and women'),
('Dresses', 'Elegant dresses for women'),
('Accessories', 'Fashion accessories including watches and bags'),
('Shoes', 'Fashionable footwear for all occasions');

-- Insert sample products
INSERT INTO products (name, description, price, sale_price, category_id, image_url, stock_quantity, is_featured, is_new, is_sale, rating, rating_count) VALUES 
('Premium Black Hoodie', 'High-quality black hoodie made from premium materials', 1499, NULL, 1, '../images/hoodie-black.png', 50, 1, 1, 0, 4.5, 24),
('Casual Green Hoodie', 'Comfortable green hoodie for everyday wear', 1299, 1599, 1, '../images/hoodie-green.png', 35, 1, 0, 1, 4.0, 18),
('Winter Hoodie', 'Warm hoodie perfect for cold winter days', 1799, NULL, 1, '../images/hoodie-black.png', 40, 0, 0, 0, 5.0, 32),
('Stylish Hoodie', 'Fashion-forward hoodie with modern design', 1399, NULL, 1, '../images/hoodie-black.png', 45, 0, 1, 0, 3.5, 12),
('Casual Hoodie', 'Everyday hoodie for a relaxed look', 1599, NULL, 1, '../images/hoodie-black.png', 30, 0, 0, 0, 4.0, 21),
('Premium Hoodie', 'Luxurious hoodie with exceptional comfort', 1299, 1699, 1, '../images/hoodie-black.png', 25, 1, 0, 1, 5.0, 45),
('Summer Dress', 'Light and airy dress perfect for summer', 1299, NULL, 4, '../images/hoodie-black.png', 20, 0, 1, 0, 4.5, 29),
('Stylish Handbag', 'Elegant handbag to complement any outfit', 1199, 1499, 5, '../images/hoodie-green.png', 15, 0, 0, 1, 4.0, 15),
('Casual Shoes', 'Comfortable shoes for everyday wear', 999, NULL, 6, '../images/hoodie-black.png', 40, 0, 0, 0, 5.0, 37),
('Elegant Top', 'Sophisticated top for special occasions', 899, NULL, 2, '../images/hoodie-black.png', 25, 0, 1, 0, 3.5, 19),
('Denim Jeans', 'Classic denim jeans with perfect fit', 1599, NULL, 3, '../images/hoodie-black.png', 60, 0, 0, 0, 4.0, 23),
('Designer Watch', 'Premium watch with elegant design', 2499, 2999, 5, '../images/hoodie-black.png', 10, 0, 0, 1, 5.0, 41);

-- Insert admin user (password is 'admin123' hashed with password_hash)
INSERT INTO admins (name, email, password, role) VALUES 
('Admin User', 'admin@gmail.com', '$2y$10$3lEsFK5.oKxUkw8/u2jZ5ee9vvP9zYrL9Yb0zsg9YZL9ksKKYrY7S', 'admin'); 