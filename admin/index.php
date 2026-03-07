<?php
session_start();
require_once '../config/database.php';
require_once '../config/notificaciones.php';
adminRedirect();

$pdo = getConnection();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);

// Estadísticas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM remiseros WHERE rol = 'remisero' AND activo = 1");
$totalRemiseros = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pasajeros");
$totalPasajeros = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM viajes WHERE DATE(created_at) = CURDATE()");
$viajesHoy = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM viajes WHERE estado = 'buscando'");
$buscando = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Remisería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background: #1a73e8; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        .sidebar a:hover { background: #1557b0; }
        .sidebar a.active { background: #0d47a1; }
        .stat-card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -100%; top: 0; bottom: 0; width: 250px; z-index: 1050; transition: left 0.3s; }
            .sidebar.show { left: 0; }
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1040; }
            .sidebar-overlay.show { display: block; }
            .menu-toggle { display: block !important; }
            .main-content { padding: 1rem !important; }
            h2 { font-size: 1.25rem !important; }
            .stat-card { margin-bottom: 1rem; }
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
                <a href="index.php" class="active" onclick="closeSidebar()"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
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
                <h2 class="mb-4">Dashboard - Administrador</h2>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Remiseros Activos</p>
                                    <h2 class="mb-0"><?= $totalRemiseros ?></h2>
                                </div>
                                <i class="bi bi-people fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Pasajeros</p>
                                    <h2 class="mb-0"><?= $totalPasajeros ?></h2>
                                </div>
                                <i class="bi bi-person-badge fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Viajes Hoy</p>
                                    <h2 class="mb-0"><?= $viajesHoy ?></h2>
                                </div>
                                <i class="bi bi-car-front fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Buscando</p>
                                    <h2 class="mb-0"><?= $buscando ?></h2>
                                </div>
                                <i class="bi bi-search fs-1 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Viajes Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Remisero</th>
                                    <th>Pasajero</th>
                                    <th>Tipo</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT v.*, r.nombre as nombre_remisero, p.apellido, p.nombre as nombre_pasajero 
                                    FROM viajes v 
                                    LEFT JOIN remiseros r ON v.id_remisero = r.id 
                                    LEFT JOIN pasajeros p ON v.id_pasajero = p.id 
                                    ORDER BY v.created_at DESC LIMIT 10
                                ");
                                while ($row = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['nombre_remisero']) ?></td>
                                    <td><?= $row['apellido'] ? htmlspecialchars($row['apellido'] . ' ' . $row['nombre_pasajero']) : '-' ?></td>
                                    <td><?= $row['tipo'] === 'local' ? 'Local' : 'Larga Dist.' ?></td>
                                    <td><?= htmlspecialchars($row['origen']) ?></td>
                                    <td><?= htmlspecialchars($row['destino']) ?></td>
                                    <td>
                                        <?php
                                        $estados = ['buscando' => 'warning', 'en_curso' => 'primary', 'completado' => 'success', 'cancelado' => 'danger'];
                                        ?>
                                        <span class="badge bg-<?= $estados[$row['estado']] ?>"><?= ucfirst($row['estado']) ?></span>
                                    </td>
                                    <td><?= date('d/m H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        </div>
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
