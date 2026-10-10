<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'php_db';

$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset('utf8mb4');

$conn->query(
    'CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);