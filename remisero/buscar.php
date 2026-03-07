<?php
session_start();
require_once '../config/database.php';
require_once '../config/notificaciones.php';
authRedirect();

$pdo = getConnection();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);
$message = '';
$remisero_id = $_SESSION['user_id'];

$pasajeros = $pdo->query("SELECT p.* FROM pasajeros p 
    LEFT JOIN viajes v ON p.id = v.id_pasajero AND v.estado IN ('buscando', 'en_curso') 
    WHERE v.id IS NULL 
    ORDER BY p.apellido, p.nombre LIMIT 50")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pasajero = $_POST['id_pasajero'] ?? null;
    $nuevo_pasajero = $_POST['nuevo_pasajero'] ?? '';
    
    if ($nuevo_pasajero) {
        $parts = explode(' ', trim($nuevo_pasajero), 2);
        $stmt = $pdo->prepare('INSERT INTO pasajeros (apellido, nombre, telefono, direccion) VALUES (?, ?, ?, ?)');
        $stmt->execute([$parts[0] ?? '', $parts[1] ?? '', $_POST['telefono'] ?? '', $_POST['direccion'] ?? '']);
        $id_pasajero = $pdo->lastInsertId();
    }
    
    if (!$id_pasajero) {
        $message = 'Debe seleccionar o crear un pasajero';
    } else {
        $stmt = $pdo->prepare('INSERT INTO viajes (id_remisero, id_pasajero, tipo, origen, destino, estado) VALUES (?, ?, "local", "-", "-", "buscando")');
        $stmt->execute([$remisero_id, $id_pasajero]);
        
        $stmt_pasajero = $pdo->prepare('SELECT nombre, apellido FROM pasajeros WHERE id = ?');
        $stmt_pasajero->execute([$id_pasajero]);
        $pasajero = $stmt_pasajero->fetch();
        
        $stmt_admin = $pdo->query('SELECT id FROM remiseros WHERE rol = "admin" LIMIT 1')->fetch();
        if ($stmt_admin) {
            crear_notificacion($pdo, $stmt_admin['id'], 'Nuevo viaje', $_SESSION['nombre'] . ' inició un viaje con ' . $pasajero['apellido'] . ' ' . $pasajero['nombre'], 'info');
        }
        
        $message = 'Viaje registrado. Cuando busques al pasajero, marcá "En Curso".';
        $pasajeros = $pdo->query("SELECT p.* FROM pasajeros p 
            LEFT JOIN viajes v ON p.id = v.id_pasajero AND v.estado IN ('buscando', 'en_curso') 
            WHERE v.id IS NULL 
            ORDER BY p.apellido, p.nombre LIMIT 50")->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Viaje - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .top-bar { background: #1a73e8; color: white; padding: 15px 0; }
        @media (max-width: 768px) {
            .top-bar .container { flex-direction: column; gap: 10px; text-align: center; }
            .top-bar .d-flex { flex-direction: column !important; gap: 10px !important; }
            .card-body { padding: 1rem !important; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div><a href="index.php" class="text-white text-decoration-none"><i class="bi bi-arrow-left"></i></a> <strong class="ms-3">Nuevo Viaje</strong></div>
            <div>
                <?php render_notificaciones($pdo, $id_usuario); ?>
                <span class="me-3"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="perfil.php" class="btn btn-sm btn-outline-light"><i class="bi bi-gear"></i></a>
                <a href="../logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?= strpos($message, 'registrado') !== false ? 'success' : 'danger' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Registrar Pasajero</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Pasajero</label>
                        <select name="id_pasajero" class="form-select" id="selectPasajero" onchange="toggleNuevoPasajero()">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($pasajeros as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['apellido'] . ' ' . $p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-2"><small class="text-muted">O crear nuevo:</small></div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido y Nombre</label>
                            <input type="text" name="nuevo_pasajero" class="form-control" placeholder="Apellido Nombre" id="inputNuevoPasajero">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100"><i class="bi bi-check-circle"></i> Iniciar Viaje (Buscar Pasajero)</button>
        </form>

        <?php if (empty($pasajeros)): ?>
        <div class="alert alert-info mt-3">Todos los pasajeros tienen un viaje activo.</div>
        <?php endif; ?>
        
        <div class="mt-3 text-center">
            <a href="mis_viajes.php" class="btn btn-outline-primary"><i class="bi bi-list-check"></i> Ver Mis Viajes</a>
        </div>
    </div>

    <script>
        function toggleNuevoPasajero() {
            const select = document.getElementById('selectPasajero');
            const input = document.getElementById('inputNuevoPasajero');
            if (select.value) {
                input.value = '';
                input.placeholder = '(Se usará el seleccionado)';
            } else {
                input.placeholder = 'Apellido Nombre';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
