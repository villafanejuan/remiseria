<?php
function render_notificaciones($pdo, $id_usuario) {
    $notificaciones = obtener_notificaciones($pdo, $id_usuario);
    $no_leidas = contar_notificaciones($pdo, $id_usuario);
    ?>
    <div class="dropdown d-inline-block me-2">
        <button class="btn btn-outline-light position-relative p-2" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; width: 40px; height: 40px;">
            <i class="bi bi-bell fs-5"></i>
            <?php if ($no_leidas > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; min-width: 18px; height: 18px; line-height: 18px; padding: 0 4px;">
                <?= $no_leidas > 9 ? '9+' : $no_leidas ?>
            </span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end notifications-dropdown shadow" style="width: 350px; max-height: 400px; overflow-y: auto; border-radius: 10px; border: none;">
            <li class="dropdown-header d-flex justify-content-between align-items-center py-3 px-3" style="background: #f8f9fa; border-bottom: 1px solid #eee;">
                <span class="fw-bold"><i class="bi bi-bell me-2"></i>Notificaciones</span>
                <?php if ($no_leidas > 0): ?>
                <a href="?marcar_leidas=1" class="btn btn-sm btn-link text-decoration-none text-primary">Marcar todo</a>
                <?php endif; ?>
            </li>
            <?php if (empty($notificaciones)): ?>
            <li><span class="dropdown-item text-muted text-center py-4"><i class="bi bi-bell-slash fs-4 d-block mb-2"></i>Sin notificaciones</span></li>
            <?php else: ?>
                <?php foreach ($notificaciones as $n): ?>
                <li>
                    <a href="?notif_id=<?= $n['id'] ?>" class="dropdown-item <?= $n['leida'] ? 'text-muted' : '' ?>" style="<?= !$n['leida'] ? 'background: #f0f7ff; border-left: 3px solid #1a73e8;' : '' ?>">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background: <?= $n['tipo'] === 'success' ? '#d4edda' : ($n['tipo'] === 'warning' ? '#fff3cd' : ($n['tipo'] === 'danger' ? '#f8d7da' : '#cce5ff')) ?>;">
                                <i class="bi bi-<?= $n['tipo'] === 'success' ? 'check-circle' : ($n['tipo'] === 'warning' ? 'exclamation-triangle' : ($n['tipo'] === 'danger' ? 'x-circle' : 'info-circle')) ?> text-<?= $n['tipo'] ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small"><?= htmlspecialchars($n['titulo']) ?></div>
                                <small class="text-muted d-block"><?= htmlspecialchars($n['mensaje']) ?></small>
                                <small class="text-muted" style="font-size: 11px;"><?= date('d/m H:i', strtotime($n['created_at'])) ?></small>
                            </div>
                            <?php if (!$n['leida']): ?>
                            <div class="ms-auto"><span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px; padding: 0;"></span></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <?php
}

function handle_notificaciones($pdo, $id_usuario) {
    if (isset($_GET['marcar_leidas'])) {
        $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = ? AND leida = 0");
        $stmt->execute([$id_usuario]);
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
    if (isset($_GET['notif_id'])) {
        marcar_notificacion_leida($pdo, $_GET['notif_id'], $id_usuario);
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}
