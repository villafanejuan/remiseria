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
if (!empty($_GET['tipo'])) {
    $where .= ' AND v.tipo = ?';
    $params[] = $_GET['tipo'];
}

$stmt = $pdo->prepare("SELECT v.*, r.nombre as nombre_remisero, p.apellido, p.nombre as nombre_pasajero FROM viajes v LEFT JOIN remiseros r ON v.id_remisero = r.id LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE $where ORDER BY v.created_at DESC");
$stmt->execute($params);
$viajes = $stmt->fetchAll();
$remiseros = $pdo->query('SELECT * FROM remiseros WHERE activo = 1')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viajes - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background: #1a73e8; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #1557b0; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -100%; top: 0; bottom: 0; width: 250px; z-index: 1050; transition: left 0.3s; }
            .sidebar.show { left: 0; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1040; }
            .sidebar-overlay.show { display: block; }
            .menu-toggle { display: block !important; }
            .main-content { padding: 1rem !important; }
            h2 { font-size: 1.25rem !important; }
            .card { margin-bottom: 1rem !important; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0" id="sidebar">
                <div class="text-center py-4 border-bottom border-light">
                    <i class="bi bi-car-front-fill fs-2"></i>
                    <h5 class="mt-2">Remisería</h5>
                </div>
                <a href="index.php" onclick="closeSidebar()"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="remiseros.php" onclick="closeSidebar()"><i class="bi bi-people me-2"></i> Remiseros</a>
                <a href="pasajeros.php" onclick="closeSidebar()"><i class="bi bi-person-badge me-2"></i> Pasajeros</a>
                <a href="viajes.php" class="active" onclick="closeSidebar()"><i class="bi bi-car me-2"></i> Viajes</a>
                <a href="nuevo_viaje.php" onclick="closeSidebar()"><i class="bi bi-plus-circle me-2"></i> Nuevo Viaje</a>
                <a href="reportes.php" onclick="closeSidebar()"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                <a href="perfil.php" onclick="closeSidebar()"><i class="bi bi-gear me-2"></i> Perfil</a>
                <a href="../logout.php" onclick="closeSidebar()"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4 main-content">
                <button class="btn btn-primary menu-toggle mb-3" onclick="toggleSidebar()" style="display:none;"><i class="bi bi-list fs-4"></i></button>
                <h2 class="mb-4"><i class="bi bi-car me-2"></i>Todos los Viajes</h2>
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-6 col-md-3"><label class="form-label">Fecha Inicio</label><input type="date" name="fecha_inicio" class="form-control" value="<?= $_GET['fecha_inicio'] ?? '' ?>"></div>
                            <div class="col-6 col-md-2"><label class="form-label">Fecha Fin</label><input type="date" name="fecha_fin" class="form-control" value="<?= $_GET['fecha_fin'] ?? '' ?>"></div>
                            <div class="col-6 col-md-2"><label class="form-label">Tipo</label><select name="tipo" class="form-select"><option value="">Todos</option><option value="local" <?= ($_GET['tipo'] ?? '') === 'local' ? 'selected' : '' ?>>Local</option><option value="larga_distancia" <?= ($_GET['tipo'] ?? '') === 'larga_distancia' ? 'selected' : '' ?>>Larga Distancia</option></select></div>
                            <div class="col-6 col-md-3"><label class="form-label">Remisero</label><select name="remisero" class="form-select"><option value="">Todos</option><?php foreach ($remiseros as $r): ?><option value="<?= $r['id'] ?>" <?= ($_GET['remisero'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-12 col-md-2"><label class="form-label">&nbsp;</label><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button></div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead><tr><th>ID</th><th>Remisero</th><th>Pasajero</th><th>Tipo</th><th>Origen</th><th>Destino</th><th>Estado</th><th>Fecha</th></tr></thead>
                            <tbody>
                                <?php foreach ($viajes as $v): ?>
                                <tr class="<?= $v['tipo'] === 'larga_distancia' ? 'table-warning' : '' ?>">
                                    <td><?= $v['id'] ?></td>
                                    <td><?= htmlspecialchars($v['nombre_remisero']) ?></td>
                                    <td><?= $v['apellido'] ? htmlspecialchars($v['apellido'] . ' ' . $v['nombre_pasajero']) : '-' ?></td>
                                    <td><span class="badge bg-<?= $v['tipo'] === 'larga_distancia' ? 'warning text-dark' : 'info' ?>"><?= $v['tipo'] === 'local' ? 'Local' : 'Larga Dist.' ?></span></td>
                                    <td><?= htmlspecialchars($v['origen']) ?></td>
                                    <td><?= htmlspecialchars($v['destino']) ?></td>
                                    <td><span class="badge bg-<?= $v['estado'] === 'completado' ? 'success' : ($v['estado'] === 'cancelado' ? 'danger' : ($v['estado'] === 'buscando' ? 'warning' : 'primary')) ?>"><?= ucfirst($v['estado']) ?></span></td>
                                    <td><?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); document.querySelector('.sidebar-overlay').classList.toggle('show'); }
        function closeSidebar() { document.getElementById('sidebar').classList.remove('show'); document.querySelector('.sidebar-overlay').classList.remove('show'); }
    </script>
</body>
</html>