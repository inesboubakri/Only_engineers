-- Run this script in phpMyAdmin to create the courses table

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS database_name;
USE database_name;

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    fees DECIMAL(10,2) DEFAULT 0,
    course_link VARCHAR(255),
    certification_link VARCHAR(255),
    status ENUM('free', 'paid') DEFAULT 'free',
    icon VARCHAR(10) DEFAULT '📚',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO courses (course_id, title, fees, course_link, certification_link, status, icon) 
VALUES 
('CRS-001', 'Introduction to Web Development', 0, 'https://example.com/web-dev', 'https://example.com/cert/web-dev', 'free', '💻'),
('CRS-002', 'Advanced JavaScript', 99.99, 'https://example.com/js-advanced', 'https://example.com/cert/js-advanced', 'paid', '⚡'),
('CRS-003', 'Data Science Essentials', 149.99, 'https://example.com/data-science', 'https://example.com/cert/data-science', 'paid', '📊'),
('CRS-004', 'Mobile App Development with React Native', 129.99, 'https://example.com/react-native', 'https://example.com/cert/react-native', 'paid', '📱'),
('CRS-005', 'Python for Beginners', 0, 'https://example.com/python-basics', 'https://example.com/cert/python-basics', 'free', '🐍');
