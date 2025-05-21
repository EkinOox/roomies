CREATE DATABASE IF NOT EXISTS app_db;
USE app_db;

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Exemples d'insertion (mot de passe hashé SHA-256 en string brut ici, à adapter selon Symfony)
INSERT INTO user (name, email, password) VALUES
('Jean Dupont', 'jean@example.com', 'password123'),
('Alice Martin', 'alice@example.com', 'alicepwd');
