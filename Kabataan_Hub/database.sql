CREATE DATABASE IF NOT EXISTS kabataan_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kabataan_hub;

CREATE TABLE IF NOT EXISTS users(
 id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(150) UNIQUE NOT NULL,
 password VARCHAR(255) NOT NULL,role ENUM('youth','admin') DEFAULT 'youth',
 phone VARCHAR(30) DEFAULT '',address VARCHAR(255) DEFAULT '',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS announcements(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200),content TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS events(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200),event_date DATE,location VARCHAR(200),description TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS registrations(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,event_id INT,status VARCHAR(30) DEFAULT 'registered',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY u(user_id,event_id));
CREATE TABLE IF NOT EXISTS programs(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200),description TEXT,status VARCHAR(50) DEFAULT 'Active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS opportunities(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200),description TEXT,link VARCHAR(500),status VARCHAR(50) DEFAULT 'Active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS feedback(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,subject VARCHAR(200),message TEXT,status VARCHAR(30) DEFAULT 'Pending',admin_reply TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS service_requests(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,service_type VARCHAR(150),details TEXT,status VARCHAR(30) DEFAULT 'Pending',admin_notes TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS projects(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(200),description TEXT,budget DECIMAL(12,2) DEFAULT 0,status VARCHAR(50) DEFAULT 'Ongoing',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS notifications(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,title VARCHAR(200) NOT NULL,message TEXT NOT NULL,is_read TINYINT(1) DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT IGNORE INTO users(name,email,password,role) VALUES('System Administrator','admin@kabataan.local',MD5('admin123'),'admin');
INSERT INTO announcements(title,content) SELECT 'Welcome to Kabataan Hub','Centralized SK information and youth engagement portal.' WHERE NOT EXISTS(SELECT 1 FROM announcements);
INSERT INTO events(title,event_date,location,description) SELECT 'Youth Leadership Seminar','2026-10-10','Barangay Hall','Leadership and community development seminar.' WHERE NOT EXISTS(SELECT 1 FROM events);
INSERT INTO events(title,event_date,location,description) SELECT 'SK Sports Festival','2026-10-20','Covered Court','Youth sports activities.' WHERE NOT EXISTS(SELECT 1 FROM events WHERE title='SK Sports Festival');
INSERT INTO programs(title,description) SELECT 'Youth Development Program','Leadership, education, sports, and community participation.' WHERE NOT EXISTS(SELECT 1 FROM programs);
INSERT INTO opportunities(title,description,link) SELECT 'Youth Training Opportunity','Skills training and development opportunity.','#' WHERE NOT EXISTS(SELECT 1 FROM opportunities);
INSERT INTO projects(title,description,budget,status) SELECT 'Community Sports Equipment','Equipment for youth sports activities.',50000,'Completed' WHERE NOT EXISTS(SELECT 1 FROM projects);
