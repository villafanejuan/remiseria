<?php
session_start();
require_once '../config/database.php';
adminRedirect();

$pdo = getConnection();
$message = '';
$remiseros = $pdo->query('SELECT * FROM remiseros WHERE activo = 1 AND rol = "remisero"')->fetchAll();
$pasajeros = $pdo->query('SELECT * FROM pasajeros ORDER BY apellido, nombre')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_remisero = $_POST['id_remisero'] ?? null;
    $id_pasajero = $_POST['id_pasajero'] ?? null;
    $nuevo_pasajero = $_POST['nuevo_pasajero'] ?? '';
    $tipo = $_POST['tipo'] ?? 'local';
    
    if (!$id_remisero) {
        $message = 'Debe seleccionar un remisero';
    } elseif (!$id_pasajero && !$nuevo_pasajero) {
        $message = 'Debe seleccionar o crear un pasajero';
    } else {
        if ($nuevo_pasajero) {
            $parts = explode(' ', trim($nuevo_pasajero), 2);
            $stmt = $pdo->prepare('INSERT INTO pasajeros (apellido, nombre, telefono, direccion) VALUES (?, ?, ?, ?)');
            $stmt->execute([$parts[0] ?? '', $parts[1] ?? '', $_POST['telefono'] ?? '', $_POST['direccion'] ?? '']);
            $id_pasajero = $pdo->lastInsertId();
        }
        
        $origen = $_POST['origen'] ?? '';
        $destino = $_POST['destino'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';
        
        if ($tipo === 'larga_distancia' && (empty($origen) || empty($destino))) {
            $message = 'Para larga distancia, origen y destino son obligatorios';
        } else {
            $estado = $tipo === 'larga_distancia' ? 'buscando' : 'buscando';
            $stmt = $pdo->prepare('INSERT INTO viajes (id_remisero, id_pasajero, tipo, origen, destino, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$id_remisero, $id_pasajero, $tipo, $origen ?: '-', $destino ?: '-', $estado, $observaciones]);
            $message = $tipo === 'larga_distancia' ? 'Viaje de larga distancia registrado. El remisero puede marcar cuando busque al pasajero.' : 'Viaje registrado. El remisero completará los datos.';
        }
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
                </div>
                <a href="index.php" onclick="closeSidebar()"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="remiseros.php" onclick="closeSidebar()"><i class="bi bi-people me-2"></i> Remiseros</a>
                <a href="pasajeros.php" onclick="closeSidebar()"><i class="bi bi-person-badge me-2"></i> Pasajeros</a>
                <a href="viajes.php" onclick="closeSidebar()"><i class="bi bi-car me-2"></i> Viajes</a>
                <a href="nuevo_viaje.php" onclick="closeSidebar()"><i class="bi bi-plus-circle me-2"></i> Nuevo Viaje</a>
                <a href="reportes.php" onclick="closeSidebar()"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                <a href="perfil.php" onclick="closeSidebar()"><i class="bi bi-gear me-2"></i> Perfil</a>
                <a href="../logout.php" onclick="closeSidebar()"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4 main-content">
                <button class="btn btn-primary menu-toggle mb-3" onclick="toggleSidebar()" style="display:none;"><i class="bi bi-list fs-4"></i></button>
                <h2 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Nuevo Viaje</h2>
                <?php if ($message): ?>
                    <div class="alert alert-<?= strpos($message, 'exitosamente') !== false || strpos($message, 'registrado') !== false ? 'success' : 'danger' ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Datos del Remisero</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Seleccionar Remisero</label>
                                <select name="id_remisero" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($remiseros as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Datos del Pasajero</h5>
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

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-car me-2"></i>Datos del Viaje</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Viaje</label>
                                <select name="tipo" id="tipoViaje" class="form-select" onchange="toggleCampos()">
                                    <option value="local">Local (Servicio común)</option>
                                    <option value="larga_distancia">Larga Distancia (Con compromiso)</option>
                                </select>
                            </div>
                            <div class="mb-3" id="origenDiv">
                                <label class="form-label" id="labelOrigen">Origen</label>
                                <input type="text" name="origen" class="form-control" id="inputOrigen">
                                <small class="text-muted" id="helpOrigen">Opcional</small>
                            </div>
                            <div class="mb-3" id="destinoDiv">
                                <label class="form-label" id="labelDestino">Destino</label>
                                <input type="text" name="destino" class="form-control" id="inputDestino">
                                <small class="text-muted" id="helpDestino">Opcional</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100"><i class="bi bi-check-circle"></i> Registrar Viaje</button>
                </form>
            </div>
        </div>
    </div>
<script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); document.querySelector('.sidebar-overlay').classList.toggle('show'); }
        function closeSidebar() { document.getElementById('sidebar').classList.remove('show'); document.querySelector('.sidebar-overlay').classList.remove('show'); }
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
        function toggleCampos() {
            const tipo = document.getElementById('tipoViaje').value;
            const inputOrigen = document.getElementById('inputOrigen');
            const inputDestino = document.getElementById('inputDestino');
            const labelOrigen = document.getElementById('labelOrigen');
            const labelDestino = document.getElementById('labelDestino');
            const helpOrigen = document.getElementById('helpOrigen');
            const helpDestino = document.getElementById('helpDestino');
            
            if (tipo === 'larga_distancia') {
                labelOrigen.textContent = 'Origen (Dónde buscar al pasajero) *';
                labelDestino.textContent = 'Destino (Dónde llevar al pasajero) *';
                helpOrigen.textContent = 'Obligatorio';
                helpDestino.textContent = 'Obligatorio';
                inputOrigen.required = true;
                inputDestino.required = true;
            } else {
                labelOrigen.textContent = 'Origen';
                labelDestino.textContent = 'Destino';
                helpOrigen.textContent = 'Opcional';
                helpDestino.textContent = 'Opcional';
                inputOrigen.required = false;
                inputDestino.required = false;
            }
        }
    </script>
</body>
</html>
