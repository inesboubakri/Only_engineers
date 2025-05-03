-- setup.sql
-- Create the database
CREATE DATABASE IF NOT EXISTS onlyengs;
USE onlyengs;

-- Create the users table to store all form data
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL, -- User's full name
    email VARCHAR(100) NOT NULL UNIQUE, -- User's email (unique)
    password VARCHAR(255) NOT NULL, -- Hashed password
    profile_picture VARCHAR(255), -- Path or URL to profile picture
    position VARCHAR(100) NOT NULL, -- Position/Title
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    birthday DATE NOT NULL,
    about TEXT NOT NULL, -- About section (10-255 words)
    seeking TEXT NOT NULL, -- Comma-separated list for seeking options (e.g., "network, job")
    experiences TEXT, -- JSON array of experience objects as text
    educations TEXT, -- JSON array of education objects as text
    organizations TEXT, -- JSON array of organization objects as text
    honors TEXT, -- JSON array of honor/award objects as text
    courses TEXT, -- JSON array of course objects as text
    projects TEXT, -- JSON array of project objects as text
    languages TEXT, -- JSON array of language objects as text
    skills TEXT, -- JSON array of skill objects as text
    is_admin TINYINT DEFAULT 0, -- 0 for users, 1 for admins
    is_banned TINYINT DEFAULT 0, -- 0 for active users, 1 for banned users
    reset_token VARCHAR(64) DEFAULT NULL, -- Token for password reset
    reset_token_expires TIMESTAMP NULL DEFAULT NULL, -- Expiration time for reset token
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);