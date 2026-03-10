<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

$pdo = getConnection();

$password = password_hash('admin123', PASSWORD_DEFAULT);

try {
    $pdo->exec("DELETE FROM remiseros WHERE username = 'admin' AND tenant_id = 1");
    $pdo->exec("INSERT INTO remiseros (tenant_id, nombre, username, password, rol, activo) VALUES (1, 'Admin Demo', 'admin', '$password', 'admin', 1)");
    echo "Usuario recreado: admin / admin123";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
