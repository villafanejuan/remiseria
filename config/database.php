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

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verificar_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function crear_notificacion($pdo, $id_usuario, $titulo, $mensaje, $tipo = 'info', $link = null) {
    $stmt = $pdo->prepare("INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_usuario, $titulo, $mensaje, $tipo, $link]);
}

function obtener_notificaciones($pdo, $id_usuario, $no_leidas = false) {
    $sql = "SELECT * FROM notificaciones WHERE id_usuario = ?";
    if ($no_leidas) $sql .= " AND leida = 0";
    $sql .= " ORDER BY created_at DESC LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    return $stmt->fetchAll();
}

function contar_notificaciones($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE id_usuario = ? AND leida = 0");
    $stmt->execute([$id_usuario]);
    return $stmt->fetch()['total'];
}

function marcar_notificacion_leida($pdo, $id_notif, $id_usuario) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$id_notif, $id_usuario]);
}
