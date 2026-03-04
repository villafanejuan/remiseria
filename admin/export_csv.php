<?php
session_start();
require_once '../config/database.php';
adminRedirect();

$pdo = getConnection();

$where = '1=1';
$params = [];
if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $where .= ' AND DATE(v.created_at) BETWEEN ? AND ?';
    $params[] = $_GET['fecha_inicio'];
    $params[] = $_GET['fecha_fin'];
}
if (!empty($_GET['remisero'])) {
    $where .= ' AND v.id_remisero = ?';
    $params[] = $_GET['remisero'];
}

$stmt = $pdo->prepare("SELECT v.*, r.nombre as nombre_remisero, p.apellido, p.nombre as nombre_pasajero, p.telefono FROM viajes v LEFT JOIN remiseros r ON v.id_remisero = r.id LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE $where ORDER BY v.created_at DESC");
$stmt->execute($params);
$viajes = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_viajes_' . date('Ymd') . '.csv');
echo "ï»¿";
echo "ID;Remisero;Pasajero;Telefono;Tipo;Origen;Destino;Estado;Fecha
";
foreach ($viajes as $v) {
    echo $v['id'] . ';';
    echo '"' . $v['nombre_remisero'] . '";';
    echo '"' . ($v['apellido'] ? $v['apellido'] . ' ' . $v['nombre_pasajero'] : 'Sin datos') . '";';
    echo '"' . ($v['telefono'] ?? '') . '";';
    echo ($v['tipo'] === 'local' ? 'Local' : 'Larga Distancia') . ';';
    echo '"' . $v['origen'] . '";';
    echo '"' . $v['destino'] . '";';
    echo $v['estado'] . ';';
    echo date('d/m/Y H:i', strtotime($v['created_at'])) . "
";
}
