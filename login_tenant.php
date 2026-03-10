<?php
session_start();
require_once 'config/database.php';

$slug = getTenantFromUrl();

if ($slug && file_exists("{$slug}/index.php")) {
    require "{$slug}/index.php";
    exit;
}

if ($slug) {
    $pdo = getConnection();
    $tenant = loadTenantBySlug($slug, $pdo);
    if ($tenant) {
        $loginFile = __DIR__ . '/login_tenant.php';
        require $loginFile;
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT r.*, t.slug as tenant_slug, t.nombre as tenant_nombre, t.color_principal 
                                FROM remiseros r 
                                JOIN tenants t ON r.tenant_id = t.id 
                                WHERE r.username = ? AND r.activo = 1 AND t.activo = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['tenant_data'] = [
                'id' => $user['tenant_id'],
                'slug' => $user['tenant_slug'],
                'nombre' => $user['tenant_nombre'],
                'color_principal' => $user['color_principal']
            ];

            if ($user['rol'] === 'admin') {
                redirect("/{$user['tenant_slug']}/admin/index.php");
            } else {
                redirect("/{$user['tenant_slug']}/remisero/index.php");
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Complete todos los campos';
    }
}
$tenant = getCurrentTenant();
$color = $tenant['color_principal'] ?? '#1a73e8';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($tenant['nombre'] ?? 'Remisería') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, <?= $color ?> 0%, #0d47a1 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 400px; width: 100%; }
        .login-header { background: <?= $color ?>; color: white; border-radius: 15px 15px 0 0; padding: 25px; text-align: center; }
        .login-body { padding: 25px; }
        .form-control { border-radius: 10px; padding: 12px; border: 2px solid #e0e0e0; }
        .form-control:focus { border-color: <?= $color ?>; box-shadow: none; }
        .btn-login { background: <?= $color ?>; color: white; border-radius: 10px; padding: 12px; font-weight: 600; }
        .btn-login:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-car-front-fill fs-1"></i>
            <h3 class="mt-2"><?= htmlspecialchars($tenant['nombre'] ?? 'Remisería') ?></h3>
            <p class="mb-0">Sistema de Gestión</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login w-100">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>
