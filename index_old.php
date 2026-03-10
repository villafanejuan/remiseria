<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$requestUri = $_SERVER['REQUEST_URI'];
$path = trim(parse_url($requestUri, PHP_URL_PATH), '/');
$segments = explode('/', $path);
$baseUrl = '/remiseria';

if ($path === '' || $path === 'remiseria' || $path === 'remiseria/') {
    session_start();
    
    require_once __DIR__ . '/config/database.php';
    $pdo = getConnection();
    
    $error = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE username = ? AND activo = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['super_admin'] = true;
                $_SESSION['super_admin_id'] = $admin['id'];
                echo '<script>window.location.href = "/remiseria/superadmin/"</script>';
                exit;
            }
            
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
                    echo '<script>window.location.href = "/remiseria/' . $user['tenant_slug'] . '/admin"</script>';
                } else {
                    echo '<script>window.location.href = "/remiseria/' . $user['tenant_slug'] . '/remisero"</script>';
                }
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Remisería SaaS - Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
            .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 400px; width: 100%; }
            .login-header { background: #1a73e8; color: white; border-radius: 15px 15px 0 0; padding: 25px; text-align: center; }
            .btn-login { background: #1a73e8; color: white; border-radius: 10px; padding: 12px; font-weight: 600; width: 100%; border: none; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="login-header">
                <h3>Remisería SaaS</h3>
                <p class="mb-0">Sistema de Gestión</p>
            </div>
            <div class="card-body p-4">
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
                    <button type="submit" class="btn btn-login">Ingresar</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($segments[0]) && $segments[0] === 'logout') {
    session_start();
    session_destroy();
    echo '<script>window.location.href = "/remiseria/"</script>';
    exit;
}

if (isset($segments[0]) && $segments[0] === 'setup') {
    require_once __DIR__ . '/setup.php';
    exit;
}

if (isset($segments[0]) && $segments[0] === 'superadmin') {
    session_start();
    if (!isset($_SESSION['super_admin'])) {
        echo '<script>window.location.href = "/remiseria/"</script>';
        exit;
    }
    require_once __DIR__ . '/superadmin/index.php';
    exit;
}

if (isset($segments[1]) && $segments[1] === 'admin') {
    $slug = $segments[0];
    session_start();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_data']) || $_SESSION['tenant_data']['slug'] !== $slug) {
        echo '<script>window.location.href = "/remiseria/' . $slug . '/login"</script>';
        exit;
    }
    
    require_once __DIR__ . '/admin/index.php';
    exit;
}

if (isset($segments[1]) && $segments[1] === 'remisero') {
    $slug = $segments[0];
    session_start();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['tenant_data']) || $_SESSION['tenant_data']['slug'] !== $slug) {
        echo '<script>window.location.href = "/remiseria/' . $slug . '/login"</script>';
        exit;
    }
    
    require_once __DIR__ . '/remisero/index.php';
    exit;
}

if (isset($segments[1]) && $segments[1] === 'login') {
    $slug = $segments[0];
    session_start();
    
    require_once __DIR__ . '/config/database.php';
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE slug = ? AND activo = 1");
    $stmt->execute([$slug]);
    $tenant = $stmt->fetch();
    
    if (!$tenant) {
        echo '<script>window.location.href = "/remiseria/"</script>';
        exit;
    }
    
    $error = '';
    $color = $tenant['color_principal'] ?? '#1a73e8';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT r.*, t.slug as tenant_slug, t.nombre as tenant_nombre, t.color_principal 
                FROM remiseros r 
                JOIN tenants t ON r.tenant_id = t.id 
                WHERE r.username = ? AND r.activo = 1 AND t.activo = 1 AND t.slug = ?");
            $stmt->execute([$username, $slug]);
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
                    echo '<script>window.location.href = "/remiseria/' . $slug . '/admin"</script>';
                } else {
                    echo '<script>window.location.href = "/remiseria/' . $slug . '/remisero"</script>';
                }
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - <?= htmlspecialchars($tenant['nombre']) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, <?= $color ?> 0%, #0d47a1 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
            .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 400px; width: 100%; }
            .login-header { background: <?= $color ?>; color: white; border-radius: 15px 15px 0 0; padding: 25px; text-align: center; }
            .btn-login { background: <?= $color ?>; color: white; border-radius: 10px; padding: 12px; font-weight: 600; width: 100%; border: none; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="login-header">
                <h3><?= htmlspecialchars($tenant['nombre']) ?></h3>
                <p class="mb-0">Sistema de Gestión</p>
            </div>
            <div class="card-body p-4">
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
                    <button type="submit" class="btn btn-login">Ingresar</button>
                </form>
                <div class="text-center mt-3">
                    <a href="/remiseria/" style="color: <?= $color ?>">Volver</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

echo '<script>window.location.href = "/remiseria/"</script>';
exit;
