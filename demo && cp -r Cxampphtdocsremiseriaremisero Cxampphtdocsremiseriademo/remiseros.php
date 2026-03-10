<?php
session_start();
require_once '../config/database.php';
require_once '../config/notificaciones.php';
requireTenant();
adminRedirect();

$pdo = getConnection();
$tenantId = getTenantId();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO remiseros (tenant_id, nombre, username, password, telefono, rol) VALUES (?, ?, ?, ?, ?, "remisero")');
        $stmt->execute([$tenantId, $_POST['nombre'], $_POST['username'], $password, $_POST['telefono']]);
        $message = 'Remisero creado exitosamente';
    } elseif ($action === 'eliminar') {
        $stmt = $pdo->prepare('DELETE FROM remiseros WHERE id = ? AND tenant_id = ?');
        $stmt->execute([$_POST['id'], $tenantId]);
        $message = 'Remisero eliminado';
    } elseif ($action === 'notificar') {
        $destinatario = $_POST['destinatario'] ?? 'todos';
        $titulo = $_POST['titulo'] ?? 'Notificación';
        $mensaje = $_POST['mensaje'] ?? '';
        $tipo = $_POST['tipo'] ?? 'info';
        
        if ($destinatario === 'todos') {
            $stmt = $pdo->prepare('SELECT id FROM remiseros WHERE rol = "remisero" AND activo = 1 AND tenant_id = ?');
            $stmt->execute([$tenantId]);
            while ($r = $stmt->fetch()) {
                crear_notificacion($pdo, $r['id'], $titulo, $mensaje, $tipo);
            }
            $message = 'Notificación enviada a todos los remiseros';
        } else {
            crear_notificacion($pdo, $destinatario, $titulo, $mensaje, $tipo);
            $message = 'Notificación enviada al remisero';
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM remiseros WHERE rol = "remisero" AND tenant_id = ? ORDER BY nombre');
$stmt->execute([$tenantId]);
$remiseros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remiseros - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background: #1a73e8; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background: #1557b0; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -100%; top: 0; bottom: 0; width: 250px; z-index: 1050; transition: left 0.3s; }
            .sidebar.show { left: 0; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1040; }
            .sidebar-overlay.show { display: block; }
            .menu-toggle { display: block !important; }
            .main-content { padding: 1rem !important; }
            h2 { font-size: 1.25rem !important; }
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
                    <div class="mt-3">
                        <?php render_notificaciones($pdo, $id_usuario); ?>
                    </div>
                </div>
                <a href="index.php" onclick="closeSidebar()"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="remiseros.php" class="active" onclick="closeSidebar()"><i class="bi bi-people me-2"></i> Remiseros</a>
                <a href="pasajeros.php" onclick="closeSidebar()"><i class="bi bi-person-badge me-2"></i> Pasajeros</a>
                <a href="viajes.php" onclick="closeSidebar()"><i class="bi bi-car me-2"></i> Viajes</a>
                <a href="nuevo_viaje.php" onclick="closeSidebar()"><i class="bi bi-plus-circle me-2"></i> Nuevo Viaje</a>
                <a href="reportes.php" onclick="closeSidebar()"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                <a href="perfil.php" onclick="closeSidebar()"><i class="bi bi-gear me-2"></i> Perfil</a>
                <a href="../logout.php" onclick="closeSidebar()"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4 main-content">
                <button class="btn btn-primary menu-toggle mb-3" onclick="toggleSidebar()" style="display:none;"><i class="bi bi-list fs-4"></i></button>
                <h2 class="mb-4">Gestión de Remiseros</h2>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#remiseroModal">
                            <i class="bi bi-plus-circle"></i> Nuevo
                        </button>
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#notifModal">
                            <i class="bi bi-bell"></i> Enviar Notificación
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead><tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php foreach ($remiseros as $r): ?>
                                <tr>
                                    <td><?= $r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                                    <td><?= htmlspecialchars($r['username']) ?></td>
                                    <td><?= htmlspecialchars($r['telefono']) ?></td>
                                    <td><span class="badge bg-<?= $r['activo'] ? 'success' : 'secondary' ?>"><?= $r['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                                    <td>
                                        <form method="POST" style="display:inline"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar?')"><i class="bi bi-trash"></i></button></form>
                                    </td>
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
    <div class="modal fade" id="notifModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-bell"></i> Enviar Notificación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="notificar">
                        <div class="mb-3">
                            <label class="form-label">Destinatario</label>
                            <select name="destinatario" class="form-select" id="destinatarioSelect">
                                <option value="todos">Todos los remiseros</option>
                                <?php foreach ($remiseros as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" name="titulo" class="form-control" required placeholder="Ej: Recordatorio importante">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensaje</label>
                            <textarea name="mensaje" class="form-control" rows="3" required placeholder="Escribí el mensaje..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="info">Información (azul)</option>
                                <option value="success">Éxito (verde)</option>
                                <option value="warning">Advertencia (amarillo)</option>
                                <option value="danger">Urgente (rojo)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Enviar</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="remiseroModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Nuevo Remisero</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Usuario</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); document.querySelector('.sidebar-overlay').classList.toggle('show'); }
        function closeSidebar() { document.getElementById('sidebar').classList.remove('show'); document.querySelector('.sidebar-overlay').classList.remove('show'); }
    </script>
</body>
</html>