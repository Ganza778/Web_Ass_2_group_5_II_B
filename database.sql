-- Create database
CREATE DATABASE IF NOT EXISTS tourstack;
USE tourstack;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('host', 'guest', 'admin') DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Home-stays table
CREATE TABLE homestays (
    id INT PRIMARY KEY AUTO_INCREMENT,
    host_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    location VARCHAR(200) NOT NULL,
    cultural_tag ENUM('Imigongo', 'volcanic stone', 'traditional', 'none') DEFAULT 'none',
    image VARCHAR(255) DEFAULT 'placeholder.jpg',
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    homestay_id INT NOT NULL,
    guest_id INT NOT NULL,
    nights INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homestay_id) REFERENCES homestays(id) ON DELETE CASCADE,
    FOREIGN KEY (guest_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample users (password = 'password123' hashed)
INSERT INTO users (name, email, password, role) VALUES
('John Host', 'host@test.com', '$2y$10$YourHashedPasswordHere', 'host'),
('Jane Guest', 'guest@test.com', '$2y$10$YourHashedPasswordHere', 'guest'),
('Admin User', 'admin@test.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Insert sample homestays
INSERT INTO homestays (host_id, title, description, price, location, cultural_tag, verified) VALUES
(1, 'Volcanic Stone Cottage', 'Cozy cottage near volcanoes', 45.00, 'Musanze', 'volcanic stone', TRUE),
(1, 'Imigongo Art Stay', 'Traditional art experience', 55.00, 'Ruhengeri', 'Imigongo', TRUE);

-- Insert sample bookings
INSERT INTO bookings (homestay_id, guest_id, nights, total_price, status) VALUES
(1, 2, 3, 135.00, 'confirmed'),
(2, 2, 2, 110.00, 'pending');