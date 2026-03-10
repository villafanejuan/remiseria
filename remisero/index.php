<?php
session_start();
if (!defined('TENANT_BASE')) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    preg_match('#/remiseria/([^/]+)/remisero#', $path, $m);
    define('TENANT_BASE', '/remiseria/' . ($m[1] ?? 'demo'));
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/notificaciones.php';
requireTenant();
authRedirect();

$pdo = getConnection();
$tenantId = getTenantId();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM viajes WHERE id_remisero = ? AND tenant_id = ? AND DATE(created_at) = CURDATE()');
$stmt->execute([$_SESSION['user_id'], $tenantId]);
$viajes_hoy = $stmt->fetch()['total'];

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM viajes WHERE id_remisero = ? AND tenant_id = ? AND estado = "buscando"');
$stmt->execute([$_SESSION['user_id'], $tenantId]);
$buscando = $stmt->fetch()['total'];

$stmt = $pdo->prepare('SELECT v.*, p.apellido, p.nombre as nombre_pasajero, p.telefono FROM viajes v LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE v.id_remisero = ? AND v.tenant_id = ? ORDER BY v.created_at DESC LIMIT 10');
$stmt->execute([$_SESSION['user_id'], $tenantId]);
$mis_viajes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remisero - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .top-bar { background: #1a73e8; color: white; padding: 15px 0; }
        .stat-card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .top-bar .container { flex-direction: column; gap: 10px; text-align: center; }
            .top-bar .d-flex { flex-direction: column !important; gap: 10px !important; }
            .stat-card { margin-bottom: 1rem; }
            h2 { font-size: 1.5rem !important; }
            .card-body { padding: 1rem !important; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div><i class="bi bi-car-front-fill fs-4 me-2"></i> <strong>Remisería</strong></div>
            <div>
                <?php render_notificaciones($pdo, $id_usuario); ?>
                <span class="me-3"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="<?= TENANT_BASE ?>/remisero/perfil.php" class="btn btn-sm btn-outline-light"><i class="bi bi-gear"></i></a>
                <a href="<?= TENANT_BASE ?>/logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4">
                <a href="<?= TENANT_BASE ?>/remisero/buscar.php" class="text-decoration-none">
                    <div class="card stat-card p-4 text-center">
                        <i class="bi bi-car-front fs-1 text-primary"></i>
                        <h5 class="mt-2">Nuevo Viaje</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= TENANT_BASE ?>/remisero/mis_viajes.php" class="text-decoration-none">
                    <div class="card stat-card p-4 text-center">
                        <i class="bi bi-plus-circle fs-1 text-success"></i>
                        <h5 class="mt-2">Nuevo Viaje</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= TENANT_BASE ?>/remisero/mis_viajes.php" class="text-decoration-none">
                    <div class="card stat-card p-4 text-center">
                        <i class="bi bi-list-check fs-1 text-warning"></i>
                        <h5 class="mt-2">Mis Viajes</h5>
                    </div>
                </a>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-0">Viajes Hoy</p><h2><?= $viajes_hoy ?></h2></div>
                        <i class="bi bi-car-front fs-1 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><p class="text-muted mb-0">Pasajeros Buscando</p><h2><?= $buscando ?></h2></div>
                        <i class="bi bi-search fs-1 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Mis Viajes Recientes</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead><tr><th>Pasajero</th><th>Tipo</th><th>Origen</th><th>Destino</th><th>Estado</th><th>Hora</th></tr></thead>
                    <tbody>
                        <?php foreach ($mis_viajes as $v): ?>
                        <tr>
                            <td><?= $v['apellido'] ? htmlspecialchars($v['apellido'] . ' ' . $v['nombre_pasajero']) : '-' ?></td>
                            <td><?= $v['tipo'] === 'local' ? 'Local' : 'Larga Dist.' ?></td>
                            <td><?= htmlspecialchars($v['origen']) ?></td>
                            <td><?= htmlspecialchars($v['destino']) ?></td>
                            <td><span class="badge bg-<?= $v['estado'] === 'completado' ? 'success' : ($v['estado'] === 'cancelado' ? 'danger' : 'warning') ?>"><?= ucfirst($v['estado']) ?></span></td>
                            <td><?= date('H:i', strtotime($v['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>