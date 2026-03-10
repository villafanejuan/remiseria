<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE username = ? AND activo = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['super_admin'] = true;
                $_SESSION['super_admin_id'] = $admin['id'];
                $_SESSION['super_admin_nombre'] = $admin['username'];
                header("Location: /remiseria/superadmin/index.php");
                exit;
            } else {
                $error = 'Credenciales incorrectas';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } else {
        $error = 'Complete todos los campos';
    }
}

if (isset($_GET['reset'])) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    try {
        $pdo->exec("DROP TABLE IF EXISTS super_admins");
        $pdo->exec("CREATE TABLE super_admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(150),
            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("INSERT INTO super_admins (username, password, email, activo) VALUES ('superadmin', '$password', 'superadmin@remiseria.com', 1)");
        $success = 'Superadmin recreado. Usuario: superadmin, Password: admin123';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - Remisería SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 400px; width: 100%; }
        .login-header { background: #1a1a1a; color: white; border-radius: 15px 15px 0 0; padding: 25px; text-align: center; }
        .login-body { padding: 25px; }
        .btn-login { background: #1a1a1a; color: white; border-radius: 10px; padding: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3 class="mt-2">Super Admin</h3>
            <p class="mb-0">Gestión de Tenants</p>
        </div>
        <div class="login-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control" required autofocus value="superadmin">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login w-100">Ingresar</button>
            </form>
            <hr>
            <a href="?reset=1" class="btn btn-warning w-100">Recrear Superadmin</a>
            <hr>
            <a href="index.php" class="btn btn-outline-secondary w-100">Volver</a>
        </div>
    </div>
</body>
</html>
