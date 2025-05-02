
-- Database: shecoderess_system

CREATE DATABASE IF NOT EXISTS shecoderess_system;
USE shecoderess_system;

-- Table: users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'viewer', 'editor', 'coordinator') DEFAULT 'admin',
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: lecturers
CREATE TABLE lecturers (
    lecturer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    designation VARCHAR(100),
    department VARCHAR(50),
    rank_level INT,
    availability TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: exam_halls
CREATE TABLE exam_halls (
    hall_id INT AUTO_INCREMENT PRIMARY KEY,
    hall_name VARCHAR(100) NOT NULL,
    capacity INT NOT NULL,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: exams
CREATE TABLE exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(10),
    subject_name VARCHAR(100),
    degree VARCHAR(50),
    date DATE,
    start_time TIME,
    end_time TIME,
    student_count INT,
    repeaters INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: allocations
CREATE TABLE allocations (
    allocation_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT,
    hall_id INT,
    supervisor_id INT,
    invigilator_ids TEXT,
    override_flag BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
    FOREIGN KEY (hall_id) REFERENCES exam_halls(hall_id),
    FOREIGN KEY (supervisor_id) REFERENCES lecturers(lecturer_id)
);

-- Table: allocation_logs
CREATE TABLE allocation_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_id INT,
    changed_by INT,
    change_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (allocation_id) REFERENCES allocations(allocation_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id)
);

-- Table: notifications
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT,
    type ENUM('email', 'sms'),
    message TEXT,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    FOREIGN KEY (recipient_id) REFERENCES lecturers(lecturer_id)
);
