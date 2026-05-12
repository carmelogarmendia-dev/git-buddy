<?php
// Configuración para el layout
$title = 'Git Buddy - Explorador';
$bodyClass = 'explorer-page';

// Función para renderizar el árbol de archivos
function renderFileTree($item, $level = 0) {
    $safeId = 'node_' . md5($item['path']);
    
    $statusClass = '';
    $statusIcon = '';
    $statusTooltip = '';
    
    if ($item['type'] === 'file') {
        switch ($item['status']) {
            case 'untracked':
                $statusClass = 'file-untracked';
                $statusIcon = '🟢 ';
                $statusTooltip = 'Nuevo archivo (sin seguimiento)';
                break;
            case 'modified':
                $statusClass = 'file-modified';
                $statusIcon = '🟡 ';
                $statusTooltip = 'Archivo modificado';
                break;
            case 'deleted':
                $statusClass = 'file-deleted';
                $statusIcon = '🔴 ';
                $statusTooltip = 'Archivo eliminado';
                break;
            default:
                $statusClass = 'file-clean';
                $statusIcon = '⚪ ';
                $statusTooltip = 'Sin cambios';
        }
    }
    
    if ($item['type'] === 'directory') {
        ?>
        <div class="file-item directory" style="margin-left: <?= $level * 20 ?>px;">
            <div class="file-label">
                <span class="toggle-dir toggle-dir-icon" data-target="<?= $safeId ?>">
                    <i class="fas fa-chevron-right"></i>
                </span>
                <input type="checkbox" class="directory-checkbox directory-checkbox-style" 
                       data-dir-path="<?= htmlspecialchars($item['path']) ?>">
                <i class="fas fa-folder text-warning"></i>
                <strong class="dir-name"><?= htmlspecialchars($item['name']) ?></strong>
                <span class="text-muted small"> (<?= count($item['children']) ?> items)</span>
            </div>
            <div id="<?= $safeId ?>" class="children children-container">
                <?php foreach ($item['children'] as $child): ?>
                    <?php renderFileTree($child, $level + 1); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    } else {
        $icon = 'fa-file';
        if ($item['extension'] === 'php') $icon = 'fa-php';
        elseif ($item['extension'] === 'js') $icon = 'fa-js';
        elseif ($item['extension'] === 'css') $icon = 'fa-css3';
        elseif ($item['extension'] === 'html') $icon = 'fa-html5';
        elseif (in_array($item['extension'], ['jpg', 'png', 'gif', 'svg'])) $icon = 'fa-image';
        ?>
        <div class="file-item file <?= $statusClass ?>" 
             style="margin-left: <?= $level * 20 + 25 ?>px;"
             data-file-path="<?= htmlspecialchars($item['path']) ?>">
            <input type="checkbox" class="file-checkbox" value="<?= htmlspecialchars($item['path']) ?>" id="file_<?= $safeId ?>">
            <label for="file_<?= $safeId ?>" class="file-label" title="<?= $statusTooltip ?>">
                <i class="fas <?= $icon ?>"></i>
                <?= $statusIcon ?><?= htmlspecialchars($item['name']) ?>
                <span class="text-muted small"> (<?= round($item['size'] / 1024, 2) ?> KB)</span>
            </label>
        </div>
        <?php
    }
}

ob_start();
?>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="fas fa-code-branch"></i> Git Buddy</span>
        <div class="text-white">
            <i class="fas fa-folder-open"></i> <?= htmlspecialchars($project_path) ?>
            <?php if (($stats['new_files'] ?? 0) > 0 || ($stats['modified_files'] ?? 0) > 0): ?>
                <span class="badge bg-warning ms-2">
                    🟢 <?= $stats['new_files'] ?? 0 ?> nuevos | 🟡 <?= $stats['modified_files'] ?? 0 ?> modificados
                </span>
            <?php endif; ?>
        </div>
        <div>
            <button class="btn btn-outline-light btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> Refrescar
            </button>
            <a href="/github/config" class="btn btn-outline-light btn-sm ms-2">
                <i class="fab fa-github"></i> GitHub
            </a>
            <a href="/" class="btn btn-outline-light btn-sm ms-2">
                <i class="fas fa-home"></i> Cambiar proyecto
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
					<h5><i class="fas fa-tree"></i> Archivos del proyecto</h5>
					<div class="form-check mt-2">
						<input type="checkbox" class="form-check-input" id="selectAll">
						<label class="form-check-label" for="selectAll"><strong>Seleccionar todos</strong></label>
					</div>
					<small class="text-muted">
						💡 <strong>Click derecho</strong> sobre cualquier archivo para añadirlo a .gitignore
					</small>
					<small class="text-muted d-block mt-1">
						💡 <strong>Sin selección</strong> → Se suben todos los archivos 🟢 nuevos y 🟡 modificados<br>
						💡 <strong>Con selección</strong> → Se suben SOLO los archivos marcados
					</small>
				</div>
                <div class="file-tree" id="fileTree">
                    <?php if (empty($structure)): ?>
                        <div class="empty-state-container">
                            <i class="fas fa-check-circle empty-state-icon"></i>
                            <h5 class="empty-state-title">¡Todo al día!</h5>
                            <p class="empty-state-text">No hay cambios pendientes para subir a GitHub en este repositorio.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($structure as $item): ?>
                            <?php renderFileTree($item, 0); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card mb-3">
                <div class="card-body">
                    <h5><i class="fas fa-chart-simple"></i> Estadísticas</h5>
                    <hr>
                    <p><i class="fas fa-file"></i> Total archivos: <strong><?= $stats['total_files'] ?? 0 ?></strong></p>
                    <p><i class="fas fa-database"></i> Tamaño total: <strong><?= $stats['total_size'] ?? '0 B' ?></strong></p>
                    <p><i class="fas fa-plus-circle text-success"></i> Nuevos: <strong><?= $stats['new_files'] ?? 0 ?></strong></p>
                    <p><i class="fas fa-edit text-warning"></i> Modificados: <strong><?= $stats['modified_files'] ?? 0 ?></strong></p>
                    <hr>
                    <p><i class="fas fa-check-circle"></i> Seleccionados: <span id="selectedCount" class="selected-count">0</span></p>
                </div>
            </div>
            
            <!-- Estado del Repositorio Remoto -->
            <div class="card mb-3 border-info">
                <div class="card-body">
                    <h6 class="card-title fw-bold small uppercase-text mb-3">
                        <i class="fas fa-globe text-info me-1"></i> Destino en GitHub
                    </h6>
                    <div id="remoteStatusContainer">
                        <div class="text-center py-2">
                            <i class="fas fa-spinner fa-spin text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-git-alt"></i> Publicar cambios</h5>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Mensaje del commit:</label>
                        <textarea class="form-control" id="commitMessage" rows="3" placeholder="Ej: Mi primer commit desde Git Buddy"></textarea>
                    </div>
                    <button class="btn btn-primary w-100" id="commitBtn" disabled>
                        <i class="fas fa-cloud-upload-alt"></i> Commit y Push a GitHub
                    </button>
                    <div class="alert alert-info small mt-3">
                        <i class="fas fa-info-circle"></i> Asegúrate de haber conectado GitHub y el repositorio remoto
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="contextMenu" class="context-menu context-menu-hidden">
    <div class="context-menu-item" id="ignoreFileBtn">
        <i class="fas fa-ban"></i> Ignorar este archivo (añadir a .gitignore)
    </div>
</div>

<div class="help-badge" id="helpBtn">
    <i class="fas fa-question"></i>
</div>
<div class="tooltip-custom" id="helpTooltip">
    <strong>🎯 Consejos rápidos:</strong><br>
    • 🟢 Verde = Archivo nuevo<br>
    • 🟡 Naranja = Archivo modificado<br>
    • ⚪ Gris = Sin cambios<br>
    • <strong>Click derecho</strong> sobre un archivo → Ignorarlo<br>
    • ✅ <strong>Carpetas clickables</strong> → Selecciona todos sus archivos<br>
    • <strong>🔁 Refrescar</strong> para ver cambios externos<br>
    • Escribe un mensaje y pulsa "Commit y Push"
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>