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
        $stmt = $pdo->prepare('UPDATE remiseros SET password = ? WHERE id = ?');
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
        body { background: #f8f9fa; }
        .top-bar { background: #1a73e8; color: white; padding: 15px 0; }
        @media (max-width: 768px) {
            .top-bar .container { flex-direction: column; gap: 10px; text-align: center; }
            .card-body { padding: 1rem !important; }
            .card { max-width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div><a href="<?= TENANT_BASE ?>/remisero/index.php" class="text-white text-decoration-none"><i class="bi bi-arrow-left"></i></a> <strong class="ms-3">Mi Perfil</strong></div>
            <div>
                <?php render_notificaciones($pdo, $id_usuario); ?>
                <a href="<?= TENANT_BASE ?>/logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="container mt-4">
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
                    <hr>
                    <h5>Cambiar Contraseña</h5>
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
                    <a href="<?= TENANT_BASE ?>/remisero/index.php" class="btn btn-secondary ms-2">Volver</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>