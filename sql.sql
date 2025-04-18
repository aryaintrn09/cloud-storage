CREATE DATABASE cloud_storage;

USE cloud_storage;

-- Tabel untuk menyimpan data pengguna
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabel untuk menyimpan data file
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    filename VARCHAR(255),
    file_path VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
