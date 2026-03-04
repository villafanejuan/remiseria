<?php
session_start();
require_once '../config/database.php';
require_once '../libraries/fpdf.php';
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

$stmt = $pdo->prepare("SELECT v.*, r.nombre as nombre_remisero, p.apellido, p.nombre as nombre_pasajero FROM viajes v LEFT JOIN remiseros r ON v.id_remisero = r.id LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE $where ORDER BY v.created_at DESC");
$stmt->execute($params);
$viajes = $stmt->fetchAll();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Viajes - Remiseria', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(26, 115, 232);
$pdf->SetTextColor(255);
$pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Remisero', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Pasajero', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Origen', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Destino', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Estado', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0);
$fill = false;
foreach ($viajes as $v) {
    $pdf->Cell(15, 7, $v['id'], 1);
    $pdf->Cell(35, 7, substr($v['nombre_remisero'], 0, 20), 1);
    $pdf->Cell(35, 7, substr(($v['apellido'] ? $v['apellido'] . ' ' . $v['nombre_pasajero'] : '-'), 0, 20), 1);
    $pdf->Cell(20, 7, $v['tipo'] === 'local' ? 'Local' : 'Larga Dist.', 1);
    $pdf->Cell(30, 7, substr($v['origen'], 0, 18), 1);
    $pdf->Cell(30, 7, substr($v['destino'], 0, 18), 1);
    $pdf->Cell(20, 7, $v['estado'], 1, 1);
}
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Total de viajes: ' . count($viajes), 0, 1);

$pdf->Output('D', 'reporte_viajes_' . date('Ymd') . '.pdf');
