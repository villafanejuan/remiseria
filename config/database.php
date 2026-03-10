<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'remiseria_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getTenantFromUrl() {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = preg_replace('#^/remiseria/#', '/', $path);  // ← NUEVA LÍNEA 8
    $segments = array_filter(explode('/', trim($path, '/')));
    $segments = array_values($segments);
    
    if (!empty($segments) && $segments[0] !== 'index.php' && $segments[0] !== 'login.php') {
        return $segments[0];
    }
    return null;
}

function getTenantFromSession() {
    return $_SESSION['tenant_id'] ?? null;
}

function setTenantSession($tenantId) {
    $_SESSION['tenant_id'] = $tenantId;
}

function getCurrentTenant() {
    if (isset($_SESSION['tenant_data'])) {
        return $_SESSION['tenant_data'];
    }
    return null;
}

function loadTenantBySlug($slug, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE slug = ? AND activo = 1");
    $stmt->execute([$slug]);
    $tenant = $stmt->fetch();
    if ($tenant) {
        $_SESSION['tenant_id'] = $tenant['id'];
        $_SESSION['tenant_data'] = $tenant;
    }
    return $tenant;
}

function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    return $pdo;
}

function requireTenant() {
    $tenantId = getTenantFromSession();
    if (!$tenantId) {
        $slug = getTenantFromUrl();
        if ($slug) {
            $pdo = getConnection();
            $tenant = loadTenantBySlug($slug, $pdo);
            if (!$tenant) {
                http_response_code(404);
                die("Tenant no encontrado");
            }
        } else {
            header("Location: " . getLoginUrl());
            exit;
        }
    }
    return $_SESSION['tenant_id'];
}

function getLoginUrl() {
    $tenantSlug = getTenantFromUrl();
    if ($tenantSlug) {
        return "/{$tenantSlug}/login.php";
    }
    return "/login.php";
}

function getBaseUrl() {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $tenantSlug = getTenantFromUrl();
    if ($tenantSlug && $tenantSlug !== 'index.php') {
        return "{$scheme}://{$host}/{$tenantSlug}";
    }
    return "{$scheme}://{$host}";
}

function isLoggedIn() { return isset($_SESSION['user_id']) && isset($_SESSION['tenant_id']); }
function isAdmin() { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'; }
function isRemisero() { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'remisero'; }
function isSuperAdmin() { return isset($_SESSION['super_admin']) && $_SESSION['super_admin'] === true; }

function getTenantId() {
    return $_SESSION['tenant_id'] ?? null;
}

function redirect($url) { header("Location: $url"); exit; }
function authRedirect() { 
    if (!isLoggedIn()) redirect(getLoginUrl()); 
}
function adminRedirect() { 
    authRedirect(); 
    if (!isAdmin()) redirect(getBaseUrl() . '/remisero/index.php'); 
}

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
    $tenantId = getTenantId();
    $stmt = $pdo->prepare("INSERT INTO notificaciones (id_usuario, tenant_id, titulo, mensaje, tipo, link) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_usuario, $tenantId, $titulo, $mensaje, $tipo, $link]);
}

function obtener_notificaciones($pdo, $id_usuario, $no_leidas = false) {
    $tenantId = getTenantId();
    $sql = "SELECT * FROM notificaciones WHERE id_usuario = ? AND tenant_id = ?";
    if ($no_leidas) $sql .= " AND leida = 0";
    $sql .= " ORDER BY created_at DESC LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario, $tenantId]);
    return $stmt->fetchAll();
}

function contar_notificaciones($pdo, $id_usuario) {
    $tenantId = getTenantId();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE id_usuario = ? AND tenant_id = ? AND leida = 0");
    $stmt->execute([$id_usuario, $tenantId]);
    return $stmt->fetch()['total'];
}

function marcar_notificacion_leida($pdo, $id_notif, $id_usuario) {
    $tenantId = getTenantId();
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id = ? AND id_usuario = ? AND tenant_id = ?");
    $stmt->execute([$id_notif, $id_usuario, $tenantId]);
}
