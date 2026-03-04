<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'remiseria_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin() { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'; }
function isRemisero() { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'remisero'; }
function redirect($url) { header("Location: $url"); exit; }
function authRedirect() { if (!isLoggedIn()) redirect('index.php'); }
function adminRedirect() { authRedirect(); if (!isAdmin()) redirect('remisero/index.php'); }
