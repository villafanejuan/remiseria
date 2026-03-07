<?php
session_start();
require_once '../config/database.php';
require_once '../config/notificaciones.php';
adminRedirect();

$pdo = getConnection();
$id_usuario = $_SESSION['user_id'];
handle_notificaciones($pdo, $id_usuario);
$remiseros = $pdo->query('SELECT * FROM remiseros WHERE activo = 1 AND rol = "remisero"')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Remisería</title>
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
                <a href="reportes.php" class="active" onclick="closeSidebar()"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                <a href="perfil.php" onclick="closeSidebar()"><i class="bi bi-gear me-2"></i> Perfil</a>
                <a href="../logout.php" onclick="closeSidebar()"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </div>
            <div class="col-md-10 p-4 main-content">
                <button class="btn btn-primary menu-toggle mb-3" onclick="toggleSidebar()" style="display:none;"><i class="bi bi-list fs-4"></i></button>
                <h2 class="mb-4">Reportes</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><h5>Generar Reporte</h5></div>
                            <div class="card-body">
                                <form method="GET" action="export_csv.php" target="_blank">
                                    <h6 class="mb-3">Exportar CSV</h6>
                                    <div class="mb-3"><label class="form-label">Fecha Inicio</label><input type="date" name="fecha_inicio" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Fecha Fin</label><input type="date" name="fecha_fin" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Remisero</label><select name="remisero" class="form-select"><option value="">Todos</option><?php foreach ($remiseros as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?></select></div>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><h5>Generar PDF</h5></div>
                            <div class="card-body">
                                <form method="GET" action="export_pdf.php" target="_blank">
                                    <h6 class="mb-3">Exportar PDF</h6>
                                    <div class="mb-3"><label class="form-label">Fecha Inicio</label><input type="date" name="fecha_inicio" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Fecha Fin</label><input type="date" name="fecha_fin" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Remisero</label><select name="remisero" class="form-select"><option value="">Todos</option><?php foreach ($remiseros as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?></select></div>
                                    <button type="submit" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); document.querySelector('.sidebar-overlay').classList.toggle('show'); }
        function closeSidebar() { document.getElementById('sidebar').classList.remove('show'); document.querySelector('.sidebar-overlay').classList.remove('show'); }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>