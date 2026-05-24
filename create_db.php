<?php
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `composite_model` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database 'composite_model' created or already exists.\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
