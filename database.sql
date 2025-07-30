-- LoveNet Database Setup Script
-- This script creates all the necessary tables for the LoveNet dating website

-- Create database (uncomment if you need to create the database)
-- CREATE DATABASE IF NOT EXISTS lovenet_db;
-- USE lovenet_db;

-- Users table
CREATE TABLE IF NOT EXISTS TBL_USERS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    dob DATE NOT NULL,
    bio TEXT,
    profile_pic VARCHAR(255),
    status ENUM('active', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_gender (gender),
    INDEX idx_created_at (created_at)
);

-- Admins table
CREATE TABLE IF NOT EXISTS TBL_ADMINS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'moderator') DEFAULT 'moderator',
    status ENUM('active', 'disabled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- Messages table
CREATE TABLE IF NOT EXISTS TBL_MESSAGES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    INDEX idx_sender_id (sender_id),
    INDEX idx_receiver_id (receiver_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_conversation (sender_id, receiver_id)
);

-- Reports table
CREATE TABLE IF NOT EXISTS TBL_REPORTS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reported_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    INDEX idx_reported_id (reported_id),
    INDEX idx_reporter_id (reporter_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Verifications table
CREATE TABLE IF NOT EXISTS TBL_VERIFICATIONS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selfie VARCHAR(255) NOT NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES TBL_ADMINS(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at)
);

-- User likes/matches table (optional enhancement)
CREATE TABLE IF NOT EXISTS TBL_LIKES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    liker_id INT NOT NULL,
    liked_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (liker_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    FOREIGN KEY (liked_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (liker_id, liked_id),
    INDEX idx_liker_id (liker_id),
    INDEX idx_liked_id (liked_id),
    INDEX idx_created_at (created_at)
);

-- User preferences table (optional enhancement)
CREATE TABLE IF NOT EXISTS TBL_USER_PREFERENCES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    min_age INT DEFAULT 18,
    max_age INT DEFAULT 100,
    preferred_gender ENUM('male', 'female', 'other', 'all') DEFAULT 'all',
    max_distance INT DEFAULT 50, -- in kilometers
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES TBL_USERS(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);

-- Insert sample admin user
INSERT INTO TBL_ADMINS (username, email, password, role) VALUES 
('superadmin', 'admin@lovenet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'); -- password: password

-- Insert sample users
INSERT INTO TBL_USERS (username, email, password, gender, dob, bio, profile_pic) VALUES 
('Sarah Johnson', 'sarah.j@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1996-05-15', 'Adventure seeker and coffee enthusiast. Looking for someone to explore the world with!', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=400&fit=crop&crop=face'),
('Michael Chen', 'michael.c@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1992-03-22', 'Music producer and dog lover. Passionate about creating meaningful connections.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face'),
('Emma Wilson', 'emma.w@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1998-08-10', 'Art teacher and yoga instructor. Love spending time outdoors and trying new restaurants.', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=400&fit=crop&crop=face'),
('David Brown', 'david.b@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1994-12-05', 'Software engineer who loves surfing and cooking. Looking for someone to share life\'s adventures.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=400&fit=crop&crop=face'),
('Jessica Lee', 'jessica.l@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1997-01-20', 'Bookworm and nature lover. Enjoy hiking, photography, and deep conversations.', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&h=400&fit=crop&crop=face'),
('Alex Smith', 'alex.s@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1995-07-14', 'Entrepreneur and fitness enthusiast. Love traveling and meeting new people.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=400&fit=crop&crop=face');

-- Insert sample messages
INSERT INTO TBL_MESSAGES (sender_id, receiver_id, message) VALUES 
(1, 2, 'Hi! I really enjoyed your profile. Would you like to grab coffee sometime?'),
(2, 1, 'That sounds great! How about tomorrow at 3 PM?'),
(3, 4, 'Thanks for the message! I\'d love to meet up.'),
(4, 3, 'Great! How about this weekend?'),
(5, 6, 'Hi Alex! I saw we have similar interests. Would you like to chat?'),
(6, 5, 'Absolutely! I\'d love to get to know you better.');

-- Insert sample reports
INSERT INTO TBL_REPORTS (reported_id, reporter_id, reason) VALUES 
(4, 1, 'Inappropriate messages'),
(6, 3, 'Fake profile'),
(2, 5, 'Harassment');

-- Insert sample verifications
INSERT INTO TBL_VERIFICATIONS (user_id, selfie, status) VALUES 
(1, 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face', 'verified'),
(2, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face', 'pending'),
(3, 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face', 'rejected');

-- Insert sample likes
INSERT INTO TBL_LIKES (liker_id, liked_id) VALUES 
(1, 2),
(2, 1),
(3, 4),
(4, 3),
(5, 6),
(6, 5);

-- Insert sample user preferences
INSERT INTO TBL_USER_PREFERENCES (user_id, min_age, max_age, preferred_gender, max_distance) VALUES 
(1, 25, 35, 'male', 30),
(2, 23, 32, 'female', 25),
(3, 26, 38, 'male', 40),
(4, 24, 34, 'female', 35),
(5, 25, 36, 'male', 30),
(6, 23, 33, 'female', 25);

-- Create views for easier querying
CREATE VIEW v_user_stats AS
SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
    COUNT(CASE WHEN status = 'banned' THEN 1 END) as banned_users,
    COUNT(CASE WHEN gender = 'male' THEN 1 END) as male_users,
    COUNT(CASE WHEN gender = 'female' THEN 1 END) as female_users,
    COUNT(CASE WHEN gender = 'other' THEN 1 END) as other_users
FROM TBL_USERS;

CREATE VIEW v_matches AS
SELECT 
    l1.liker_id,
    l1.liked_id,
    u1.username as liker_name,
    u2.username as liked_name,
    l1.created_at as match_date
FROM TBL_LIKES l1
INNER JOIN TBL_LIKES l2 ON l1.liker_id = l2.liked_id AND l1.liked_id = l2.liker_id
INNER JOIN TBL_USERS u1 ON l1.liker_id = u1.id
INNER JOIN TBL_USERS u2 ON l1.liked_id = u2.id
WHERE l1.liker_id < l1.liked_id;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE GetUserMatches(IN user_id INT)
BEGIN
    SELECT 
        u.id,
        u.username,
        u.profile_pic,
        u.bio,
        TIMESTAMPDIFF(YEAR, u.dob, CURDATE()) as age
    FROM TBL_USERS u
    INNER JOIN TBL_LIKES l1 ON u.id = l1.liked_id
    INNER JOIN TBL_LIKES l2 ON l1.liker_id = l2.liked_id AND l1.liked_id = l2.liker_id
    WHERE l1.liker_id = user_id AND u.status = 'active'
    ORDER BY l1.created_at DESC;
END //

CREATE PROCEDURE GetUserMessages(IN user_id INT)
BEGIN
    SELECT 
        m.*,
        u.username as sender_name,
        u.profile_pic as sender_pic
    FROM TBL_MESSAGES m
    INNER JOIN TBL_USERS u ON m.sender_id = u.id
    WHERE m.receiver_id = user_id OR m.sender_id = user_id
    ORDER BY m.sent_at DESC;
END //

DELIMITER ;

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON lovenet_db.* TO 'lovenet_user'@'localhost';
-- FLUSH PRIVILEGES; 