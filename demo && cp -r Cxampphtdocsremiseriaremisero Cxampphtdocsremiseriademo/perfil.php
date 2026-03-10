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
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $stmt = $pdo->prepare('SELECT password FROM remiseros WHERE id = ? AND tenant_id = ?');
    $stmt->execute([$_SESSION['user_id'], $tenantId]);
    $user = $stmt->fetch();
    
    if (!password_verify($current, $user['password'])) {
        $message = 'La contraseña actual es incorrecta';
    } elseif ($new !== $confirm) {
        $message = 'Las contraseñas nuevas no coinciden';
    } elseif (strlen($new) < 4) {
        $message = 'La contraseña debe tener al menos 4 caracteres';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE remiseRos SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $_SESSION['user_id']]);
        $message = 'Contraseña actualizada exitosamente';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background: #1a73e8; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background: #1557b0; }
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
                <a href="pasajeros.php" onclick="closeSidebar()"><i class="bi bi-person-badge me-2"></i> Pasajeros</a>
                <a href="viajes.php" onclick="closeSidebar()"><i class="bi bi-car me-2"></i> Viajes</a>
                <a href="nuevo_viaje.php" onclick="closeSidebar()"><i class="bi bi-plus-circle me-2"></i> Nuevo Viaje</a>
                <a href="reportes.php" onclick="closeSidebar()"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                <a href="perfil.php" class="active" onclick="closeSidebar()"><i class="bi bi-gear me-2"></i> Perfil</a>
                <a href="../logout.php" onclick="closeSidebar()"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4 main-content">
                <button class="btn btn-primary menu-toggle mb-3" onclick="toggleSidebar()" style="display:none;"><i class="bi bi-list fs-4"></i></button>
                <h2 class="mb-4">Cambiar Contraseña</h2>
                <?php if ($message): ?>
                    <div class="alert alert-<?= strpos($message, 'incorrecta') !== false || strpos($message, 'no coinciden') !== false ? 'danger' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <div class="card" style="max-width: 500px;">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['username']) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['nombre']) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña Actual</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                        </form>
                    </div>
                </div>
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