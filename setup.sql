-- Run this in your MySQL client (e.g. phpMyAdmin or mysql CLI)

CREATE DATABASE IF NOT EXISTS apesawiya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apesawiya;

-- Members table
CREATE TABLE IF NOT EXISTS members (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(255) NOT NULL,
    indexnum VARCHAR(100),
    idnum    VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL   -- bcrypt hash
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username=admin  password=admin123
-- (Generated with password_hash('admin123', PASSWORD_BCRYPT))
INSERT INTO admin (username, password)
VALUES ('admin', '$2y$10$jNx4gJaBBnuOjhRQ.XijieUP94Z9TczIHcvHmHId7zmW2Z.zyoZkG')
ON DUPLICATE KEY UPDATE username = username;
-- NOTE: Change the password immediately after first login!
