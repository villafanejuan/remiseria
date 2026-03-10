<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/config/database.php';

$pdo = getConnection();

try {
    $pdo->exec("ALTER TABLE remiseros DROP INDEX username");
    echo "Índice username eliminado (ahora permite mismo usuario en diferentes tenants)<br>";
} catch (Exception $e) {
    echo "Índice no existía o error: " . $e->getMessage() . "<br>";
}

echo "Listo! <a href='/superadmin/'>Volver al Super Admin</a>";
