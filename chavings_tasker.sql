CREATE DATABASE IF NOT EXISTS chavings_tasker;
USE chavings_tasker;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample admin user for testing
INSERT INTO users (email, password, name, is_verified) VALUES
('admin@chavings.com', '$2y$10$examplehashedpassword1234567890abcdef', 'Admin User', TRUE);