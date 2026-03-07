<?php
session_start();
require_once '../config/database.php';
require_once '../config/notificaciones.php';
authRedirect();

$pdo = getConnection();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);
$remisero_id = $_SESSION['user_id'];

$where = 'v.id_remisero = ?';
$params = [$remisero_id];

if (!empty($_GET['fecha'])) {
    $where .= ' AND DATE(v.created_at) = ?';
    $params[] = $_GET['fecha'];
}
if (!empty($_GET['estado'])) {
    $where .= ' AND v.estado = ?';
    $params[] = $_GET['estado'];
}

$stmt = $pdo->prepare("SELECT v.*, p.apellido, p.nombre as nombre_pasajero, p.telefono FROM viajes v LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE $where ORDER BY v.created_at DESC");
$stmt->execute($params);
$viajes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_viaje = $_POST['id_viaje'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if ($action === 'estado') {
        $nuevo_estado = $_POST['estado'];
        $stmt = $pdo->prepare('UPDATE viajes SET estado = ? WHERE id = ? AND id_remisero = ?');
        $stmt->execute([$nuevo_estado, $id_viaje, $remisero_id]);
        
        $stmt_viaje = $pdo->prepare('SELECT v.*, p.apellido, p.nombre FROM viajes v LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE v.id = ?');
        $stmt_viaje->execute([$id_viaje]);
        $viaje = $stmt_viaje->fetch();
        
        $stmt_admin = $pdo->query('SELECT id FROM remiseros WHERE rol = "admin" LIMIT 1')->fetch();
        if ($stmt_admin) {
            $estado_msg = $nuevo_estado === 'en_curso' ? 'en curso' : ($nuevo_estado === 'cancelado' ? 'cancelado' : $nuevo_estado);
            crear_notificacion($pdo, $stmt_admin['id'], 'Viaje actualizado', $_SESSION['nombre'] . ' marcó el viaje como ' . $estado_msg . ' (' . $viaje['apellido'] . ' ' . $viaje['nombre'] . ')', $nuevo_estado === 'cancelado' ? 'warning' : 'info');
        }
    } elseif ($action === 'pago') {
        $monto = $_POST['monto'] ?? 0;
        $metodo = $_POST['metodo_pago'] ?? 'efectivo';
        $stmt = $pdo->prepare('UPDATE viajes SET monto = ?, metodo_pago = ?, fecha_pago = NOW(), estado = "completado" WHERE id = ? AND id_remisero = ?');
        $stmt->execute([$monto, $metodo, $id_viaje, $remisero_id]);
        
        $stmt_viaje = $pdo->prepare('SELECT v.*, p.apellido, p.nombre FROM viajes v LEFT JOIN pasajeros p ON v.id_pasajero = p.id WHERE v.id = ?');
        $stmt_viaje->execute([$id_viaje]);
        $viaje = $stmt_viaje->fetch();
        
        $stmt_admin = $pdo->query('SELECT id FROM remiseros WHERE rol = "admin" LIMIT 1')->fetch();
        if ($stmt_admin) {
            crear_notificacion($pdo, $stmt_admin['id'], 'Viaje completado', $_SESSION['nombre'] . ' completó el viaje con ' . $viaje['apellido'] . ' ' . $viaje['nombre'] . ' - $' . $monto, 'success');
        }
    }
    header('Location: mis_viajes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Viajes - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .top-bar { background: #1a73e8; color: white; padding: 15px 0; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .top-bar .container { flex-direction: column; gap: 10px; text-align: center; }
            .top-bar .d-flex { flex-direction: column !important; gap: 10px !important; }
            .card-body { padding: 1rem !important; }
            .table-sm th, .table-sm td { padding: 0.5rem !important; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div><a href="index.php" class="text-white text-decoration-none"><i class="bi bi-arrow-left"></i></a> <strong class="ms-3">Mis Viajes</strong></div>
            <div>
                <?php render_notificaciones($pdo, $id_usuario); ?>
                <span class="me-3"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="perfil.php" class="btn btn-sm btn-outline-light"><i class="bi bi-gear"></i></a>
                <a href="../logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-6 col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= $_GET['fecha'] ?? '' ?>">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="buscando" <?= ($_GET['estado'] ?? '') === 'buscando' ? 'selected' : '' ?>>Buscando</option>
                            <option value="en_curso" <?= ($_GET['estado'] ?? '') === 'en_curso' ? 'selected' : '' ?>>En Curso</option>
                            <option value="completado" <?= ($_GET['estado'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelado" <?= ($_GET['estado'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php foreach ($viajes as $v): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center <?= $v['tipo'] === 'larga_distancia' ? 'bg-warning' : 'bg-light' ?>">
                <div>
                    <strong>#<?= $v['id'] ?></strong> - 
                    <?= $v['apellido'] ? htmlspecialchars($v['apellido'] . ' ' . $v['nombre_pasajero']) : 'Sin pasajero' ?>
                    <span class="badge bg-<?= $v['tipo'] === 'larga_distancia' ? 'dark' : 'info' ?>"><?= $v['tipo'] === 'local' ? 'Local' : 'L.Dist.' ?></span>
                </div>
                <span class="badge bg-<?= $v['estado'] === 'completado' ? 'success' : ($v['estado'] === 'cancelado' ? 'danger' : ($v['estado'] === 'buscando' ? 'warning' : 'primary')) ?>">
                    <?= ucfirst($v['estado']) ?>
                </span>
            </div>
            <div class="card-body">
                <?php if ($v['estado'] === 'completado'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Viaje completado
                        <?php if ($v['monto'] > 0): ?>
                            - <strong>$<?= number_format($v['monto'], 0, ',', '.') ?></strong>
                            <span class="badge bg-<?= $v['metodo_pago'] === 'efectivo' ? 'success' : 'primary' ?>">
                                <?= $v['metodo_pago'] === 'efectivo' ? 'Efectivo' : 'Transferencia' ?>
                            </span>
                        <?php else: ?>
                            - <span class="badge bg-secondary">Pago pendiente</span>
                        <?php endif; ?>
                    </div>
                <?php elseif ($v['estado'] === 'cancelado'): ?>
                    <div class="alert alert-danger mb-0"><i class="bi bi-x-circle"></i> Viaje cancelado</div>
                <?php else: ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" onchange="cambiarEstado(<?= $v['id'] ?>, this.value)">
                                <option value="buscando" <?= $v['estado'] === 'buscando' ? 'selected' : '' ?>>Buscando</option>
                                <option value="en_curso" <?= $v['estado'] === 'en_curso' ? 'selected' : '' ?>>En Curso</option>
                                <option value="completar_pagar">Completar + Pagar</option>
                                <option value="cancelado">Cancelar</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($viajes)): ?>
        <div class="alert alert-info">No hay viajes registrados</div>
        <?php endif; ?>
    </div>

    <div class="modal fade" id="pagoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_viaje" id="pagoIdViaje">
                        <input type="hidden" name="action" value="pago">
                        <div class="mb-3">
                            <label class="form-label">Monto ($)</label>
                            <input type="number" name="monto" class="form-control" required min="0" step="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Método de Pago</label>
                            <select name="metodo_pago" class="form-select">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cambiarEstado(id, action) {
            if (action === 'completar_pagar') {
                document.getElementById('pagoIdViaje').value = id;
                new bootstrap.Modal(document.getElementById('pagoModal')).show();
            } else if (action === 'buscando' || action === 'en_curso' || action === 'cancelado') {
                let mensaje = '';
                if (action === 'buscando') mensaje = '¿Marcar como Buscando?';
                else if (action === 'en_curso') mensaje = '¿Marcar como En Curso?';
                else if (action === 'cancelado') mensaje = '¿Cancelar el viaje?';
                
                if (confirm(mensaje)) {
                    fetch('mis_viajes.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id_viaje=' + id + '&action=estado&estado=' + action
                    }).then(() => window.location.reload());
                } else {
                    window.location.reload();
                }
            }
        }
    </script>
</body>
</html>
