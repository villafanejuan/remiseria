<?php
session_start();

if (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] !== true) {
    header("Location: /remiseria/");
    exit;
}

require_once __DIR__ . '/../config/database.php';
$pdo = getConnection();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_tenant'])) {
    $slug = strtolower(trim($_POST['slug'] ?? ''));
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan = $_POST['plan'] ?? 'free';
    $color = $_POST['color'] ?? '#1a73e8';
    $admin_user = trim($_POST['admin_user'] ?? 'admin');
    $admin_pass = $_POST['admin_pass'] ?? 'admin123';

    if ($slug && $nombre && $email && $admin_user && $admin_pass) {
        $stmt = $pdo->prepare("INSERT INTO tenants (slug, nombre, email, color_principal, plan) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$slug, $nombre, $email, $color, $plan])) {
            $tenantId = $pdo->lastInsertId();
            
            $password = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO remiseros (tenant_id, nombre, username, password, rol, activo) VALUES (?, ?, ?, ?, 'admin', 1)");
            $stmt->execute([$tenantId, 'Administrador', $admin_user, $password]);
            
            $message = "Tenant creado. Usuario: $admin_user, Password: $admin_pass";
        } else {
            $error = "Error al crear tenant. Verifique que el slug sea único.";
        }
    } else {
        $error = "Todos los campos son requeridos.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_superadmin'])) {
    $new_username = trim($_POST['username'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $new_email = trim($_POST['email'] ?? '');
    
    if ($new_username) {
        if ($new_password) {
            $password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE super_admins SET username = ?, password = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_username, $password, $new_email, $_SESSION['super_admin_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE super_admins SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_username, $new_email, $_SESSION['super_admin_id']]);
        }
        $_SESSION['super_admin_nombre'] = $new_username;
        $message = "Datos actualizados correctamente.";
    }
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE tenants SET activo = NOT activo WHERE id = ?");
    $stmt->execute([$id]);
}

$tenants = $pdo->query("SELECT t.*, r.username as admin_user FROM tenants t LEFT JOIN remiseros r ON t.id = r.tenant_id AND r.rol = 'admin' ORDER BY t.created_at DESC")->fetchAll();
$totalTenants = count($tenants);
$tenantsActivos = count(array_filter($tenants, fn($t) => $t['activo']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f5f5f5; }
        .sidebar { min-height: 100vh; background: #1a1a1a; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background: #333; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-4 border-bottom border-light">
                    <i class="bi bi-shield-lock fs-2 text-white"></i>
                    <h5 class="mt-2 text-white">Super Admin</h5>
                </div>
                <a href="/remiseria/"><i class="bi bi-house me-2"></i> Inicio</a>
                <a href="/remiseria/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Gestión de Tenants</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Mis Datos (Super Admin)</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="editar_superadmin" value="1">
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE id = ?");
                            $stmt->execute([$_SESSION['super_admin_id']]);
                            $superAdmin = $stmt->fetch();
                            ?>
                            <div class="col-md-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($superAdmin['username']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($superAdmin['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nueva Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Dejar vacío para mantener">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-warning">Actualizar Datos</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5>Total Tenants</h5>
                            <h2><?= $totalTenants ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5>Tenants Activos</h5>
                            <h2><?= $tenantsActivos ?></h2>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Crear Nuevo Tenant</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="crear_tenant" value="1">
                            <div class="col-md-2">
                                <label class="form-label">Slug (URL)</label>
                                <input type="text" name="slug" class="form-control" placeholder="mi-empresa" required pattern="[a-z0-9-]+">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Mi Empresa" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="admin@empresa.com" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Usuario Admin</label>
                                <input type="text" name="admin_user" class="form-control" placeholder="admin" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Password</label>
                                <input type="text" name="admin_pass" class="form-control" placeholder="password" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Plan</label>
                                <select name="plan" class="form-select">
                                    <option value="free">Free</option>
                                    <option value="basic">Basic</option>
                                    <option value="pro">Pro</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Color</label>
                                <input type="color" name="color" class="form-control form-control-color" value="#1a73e8">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Crear Tenant</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tenants Existentes</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Slug</th>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Password</th>
                                    <th>Plan</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tenants as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['slug']) ?></td>
                                    <td><?= htmlspecialchars($t['nombre']) ?></td>
                                    <td><small><?= htmlspecialchars($t['admin_user'] ?? 'admin') ?></small></td>
                                    <td><small>******</small></td>
                                    <td><span class="badge bg-<?= $t['plan'] === 'pro' ? 'success' : ($t['plan'] === 'basic' ? 'info' : 'secondary') ?>"><?= strtoupper($t['plan']) ?></span></td>
                                    <td>
                                        <?php if ($t['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/remiseria/<?= htmlspecialchars($t['slug']) ?>/admin/index.php" class="btn btn-sm btn-primary" target="_blank">Entrar</a>
                                        <a href="?toggle=<?= $t['id'] ?>" class="btn btn-sm btn-<?= $t['activo'] ? 'warning' : 'success' ?>"><?= $t['activo'] ? 'Desactivar' : 'Activar' ?></a>
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
</body>
</html>
