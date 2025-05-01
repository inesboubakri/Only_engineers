-- Create the database if not exists
CREATE DATABASE IF NOT EXISTS onlyengs;
USE onlyengs;

-- Create the users table to store all form data
CREATE TABLE IF NOT EXISTS users (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the hackathons table
CREATE TABLE IF NOT EXISTS hackathons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(100),
    required_skills TEXT,
    organizer VARCHAR(100) NOT NULL,
    max_participants INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    image VARCHAR(255),
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create the participants table
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hackathon_id INT NOT NULL,
    participation_date DATE NOT NULL,
    participation_time TIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (hackathon_id) REFERENCES hackathons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (user_id, hackathon_id)
);

