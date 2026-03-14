<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConnection();

try {
    $pdo->exec("ALTER TABLE viajes ADD COLUMN hora TIME NULL");
    echo "Columna 'hora' agregada a la tabla viajes: OK";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
