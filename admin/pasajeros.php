<?php
session_start();
require_once '../config/database.php';
require_once '../config/notificaciones.php';
adminRedirect();

$pdo = getConnection();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $stmt = $pdo->prepare("INSERT INTO pasajeros (apellido, nombre, telefono, direccion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['apellido'], $_POST['nombre'], $_POST['telefono'], $_POST['direccion']]);
        $message = 'Pasajero creado exitosamente';
    } elseif ($action === 'editar') {
        $stmt = $pdo->prepare("UPDATE pasajeros SET apellido = ?, nombre = ?, telefono = ?, direccion = ? WHERE id = ?");
        $stmt->execute([$_POST['apellido'], $_POST['nombre'], $_POST['telefono'], $_POST['direccion'], $_POST['id']]);
        $message = 'Pasajero actualizado';
    } elseif ($action === 'eliminar') {
        $stmt = $pdo->prepare("DELETE FROM pasajeros WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $message = 'Pasajero eliminado';
    }
}

$pasajeros = $pdo->query("SELECT * FROM pasajeros ORDER BY apellido, nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasajeros - Remisería</title>
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
                <a href="remiseros.php" onclick="closeSidebar()"><i class="bi bi-people me-2"></i> Remiseros</a>
                <a href="pasajeros.php" class="active" onclick="closeSidebar()"><i class="bi bi-person-badge me-2"></i> Pasajeros</a>
                <a href="viajes.php" onclick="closeSidebar()"><i class="bi bi-car me-2"></i> Viajes</a>
                <a href="nuevo_viaje.php" onclick="closeSidebar()"><i class="bi bi-plus-circle me-2"></i> Nuevo Viaje</a>
                <a href="reportes.php" onclick="closeSidebar()"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                <a href="perfil.php" onclick="closeSidebar()"><i class="bi bi-gear me-2"></i> Perfil</a>
                <a href="../logout.php" onclick="closeSidebar()"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4 main-content">
                <button class="btn btn-primary menu-toggle mb-3" onclick="toggleSidebar()" style="display:none;"><i class="bi bi-list fs-4"></i></button>
                <h2 class="mb-4">Gestión de Pasajeros</h2>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pasajeroModal">
                            <i class="bi bi-plus-circle"></i> Nuevo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead><tr><th>ID</th><th>Apellido</th><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php foreach ($pasajeros as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['apellido']) ?></td>
                                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><?= htmlspecialchars($p['telefono']) ?></td>
                                    <td><?= htmlspecialchars($p['direccion']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editar(<?= $p['id'] ?>, '<?= htmlspecialchars($p['apellido']) ?>', '<?= htmlspecialchars($p['nombre']) ?>', '<?= htmlspecialchars($p['telefono']) ?>', '<?= htmlspecialchars($p['direccion']) ?>')"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" style="display:inline"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar?')"><i class="bi bi-trash"></i></button></form>
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
    <div class="modal fade" id="pasajeroModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Nuevo Pasajero</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear"><input type="hidden" name="id" id="editId">
                        <div class="mb-3"><label class="form-label">Apellido</label><input type="text" name="apellido" id="editApellido" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="nombre" id="editNombre" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" name="telefono" id="editTelefono" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Dirección</label><input type="text" name="direccion" id="editDireccion" class="form-control"></div>
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
        function editar(id, apellido, nombre, telefono, direccion) {
            document.querySelector('#pasajeroModal .modal-title').textContent = 'Editar Pasajero';
            document.querySelector('#pasajeroModal input[name="action"]').value = 'editar';
            document.getElementById('editId').value = id;
            document.getElementById('editApellido').value = apellido;
            document.getElementById('editNombre').value = nombre;
            document.getElementById('editTelefono').value = telefono;
            document.getElementById('editDireccion').value = direccion;
            new bootstrap.Modal(document.getElementById('pasajeroModal')).show();
        }
    </script>
</body>
</html>
