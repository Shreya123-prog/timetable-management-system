-- Database: timetable_gh
CREATE DATABASE IF NOT EXISTS timetable_gh;
USE timetable_gh;

-- Users Table (Admin and Faculty)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'faculty') DEFAULT 'faculty',
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User (Password: admin123)
-- In a real app, use password_hash. For now, we'll store plain or simple hash if PHP handles it. 
-- Let's stick to simple text for the prototype as requested, or simple md5 to be slightly better, 
-- but `password_verify` in PHP is best. I will use password_hash in PHP, so here I will insert a hash for 'admin123'.
-- Hash for 'admin123' (generically generated for BCRYPT)
INSERT INTO users (username, password, role, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Admin');

-- Years (FE, SE, TE, BE)
CREATE TABLE IF NOT EXISTS years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL UNIQUE
);

INSERT INTO years (name) VALUES ('F.Y.'), ('S.Y.'), ('T.Y.'), ('B.Tech');

-- Divisions (A, B, C)
CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(5) NOT NULL UNIQUE
);

INSERT INTO divisions (name) VALUES ('A'), ('B'), ('C');

-- Subjects
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    year_id INT,
    FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE
);

-- Faculty
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100)
);

-- Classrooms
CREATE TABLE IF NOT EXISTS classrooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('Classroom', 'Lab') DEFAULT 'Classroom'
);

-- Timetable
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_id INT,
    division_id INT,
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
    time_slot VARCHAR(50), -- e.g., '09:15 - 10:15'
    subject_id INT,
    faculty_id INT,
    classroom_id INT,
    FOREIGN KEY (year_id) REFERENCES years(id),
    FOREIGN KEY (division_id) REFERENCES divisions(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (faculty_id) REFERENCES faculty(id),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id)
);
