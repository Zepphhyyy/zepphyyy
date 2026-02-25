-- Create Database
CREATE DATABASE IF NOT EXISTS country_explorer;
USE country_explorer;

-- Create Countries Table
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    capital VARCHAR(100) NOT NULL,
    flag_image LONGBLOB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Insert countries with flag images using a PHP script or import tool
-- Flags will be stored as LONGBLOB data in the flag_image column

-- Create an index for faster searches
CREATE INDEX idx_name ON countries(name);
CREATE INDEX idx_capital ON countries(capital);
