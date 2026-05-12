<?php
/**
 * VISTA PRINCIPAL (One Page)
 * Esta vista contiene la estructura de pestañas y los contenedores dinámicos.
 */
?>


<!-- Pestañas de Navegación -->
<ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-content" type="button" role="tab">
            <i class="fas fa-home"></i> Inicio
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link disabled" id="explorer-tab" data-bs-toggle="tab" data-bs-target="#explorer-content-tab" type="button" role="tab" disabled>
            <i class="fas fa-folder-open"></i> Explorador
        </button>
    </li>
</ul>

<div class="tab-content" id="mainTabsContent">
    <!-- CONTENIDO: INICIO (Selector de Proyectos) -->
    <div class="tab-pane fade show active" id="home-content" role="tabpanel">
        <div class="row g-4">
            <!-- COLUMNA IZQUIERDA: ACCIONES Y SELECCIÓN -->
            <div class="col-lg-6">
                <div class="section-title mb-3">
                    <h5><i class="fas fa-plus-circle"></i> Abrir o clonar proyecto</h5>
                </div>
                
                <!-- Selector Visual -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-body text-center p-5 folder-selector-card" id="folderSelector">
                        <i class="fas fa-folder-plus fa-3x text-primary mb-3"></i>
                        <h5>Seleccionar carpeta</h5>
                        <p class="text-muted small">Haz clic aquí para elegir un repositorio local</p>
                    </div>
                </div>

                <!-- Ruta Manual -->
                <div class="card shadow-sm mb-4 border-0 bg-light">
                    <div class="card-body">
                        <label class="form-label small fw-bold">Ruta manual:</label>
                        <div class="input-group">
                            <input type="text" id="manualPath" class="form-control form-control-sm" placeholder="C:\proyectos\mi-web">
                            <button class="btn btn-primary btn-sm" id="useManualBtn">
                                <i class="fas fa-arrow-right"></i> Abrir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas (Clonar) -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <label class="form-label small fw-bold mb-3 d-block">¿Necesitas un proyecto nuevo?</label>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-dark btn-sm text-start" data-bs-toggle="modal" data-bs-target="#cloneModal">
                                <i class="fas fa-cloud-download-alt me-2"></i> Clonar repositorio público
                            </button>
                            <button class="btn btn-outline-dark btn-sm text-start" id="cloneMyRepoBtn" data-bs-toggle="modal" data-bs-target="#cloneMyRepoModal">
                                <i class="fab fa-github me-2"></i> Mis repositorios de GitHub
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: ESTADO Y RECIENTES -->
            <div class="col-lg-6">
                <div class="section-title mb-3">
                    <h5><i class="fas fa-tasks"></i> Gestión de proyectos</h5>
                </div>

                <!-- Mensajes de estado -->
                <div id="errorMsg" class="alert alert-danger hidden-element small py-2"></div>
                <div id="loadingMsg" class="alert alert-info text-center hidden-element small py-2">
                    <i class="fas fa-spinner fa-spin me-2"></i> Cargando proyecto...
                </div>

                <!-- Proyecto Activo -->
                <div id="activeProjectSection" class="hidden-element mb-4">
                    <div class="card border-primary shadow-sm bg-primary bg-opacity-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-play-circle fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">Proyecto en curso</h6>
                                    <small class="text-primary">Sesión abierta</small>
                                </div>
                            </div>
                            <div id="activeProjectCard">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proyectos Recientes -->
                <div id="recentSection" class="hidden-element">
                    <h6 class="mb-3 text-muted fw-bold small uppercase-text tracking-wider"><i class="fas fa-history me-1"></i> Proyectos recientes</h6>
                    <div id="recentList" class="list-group shadow-sm">
                        <!-- Se llena dinámicamente con JS -->
                    </div>
                </div>

                <!-- Empty State de Gestión (si no hay nada) -->
                <div id="emptySessionState" class="text-center p-5 border rounded-3 bg-light border-dashed">
                    <i class="fas fa-ghost fa-3x text-muted mb-3 opacity-25"></i>
                    <p class="text-muted small">No hay sesiones activas ni proyectos recientes todavía.</p>
                </div>
            </div>
        </div>
    </div>


    <!-- CONTENIDO: EXPLORADOR (Carga vía AJAX) -->
    <div class="tab-pane fade" id="explorer-content-tab" role="tabpanel">
        <div id="explorerContainer">
            <!-- Se llena dinámicamente al abrir un proyecto -->
            <div class="text-center p-5">
                <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                <p class="text-muted">Abre un proyecto para ver su contenido aquí.</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CLONAR REPO PÚBLICO -->
<div class="modal fade" id="cloneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clonar repositorio público</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">URL del repositorio:</label>
                    <input type="text" id="cloneUrl" class="form-control" placeholder="https://github.com/usuario/repo.git">
                </div>
                <div class="mb-3">
                    <label class="form-label">Carpeta destino:</label>
                    <div class="input-group">
                        <input type="text" id="cloneDestino" class="form-control" placeholder="C:\proyectos\destino">
                        <button class="btn btn-outline-secondary" id="browseDestinoBtn">
                            <i class="fas fa-folder-open"></i>
                        </button>
                    </div>
                </div>
                <div id="cloneError" class="alert alert-danger hidden-element"></div>
                <div id="cloneLoading" class="text-center py-2 hidden-element">
                    <i class="fas fa-spinner fa-spin"></i> Clonando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmCloneBtn">Clonar ahora</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: MIS REPOSITORIOS -->
<div class="modal fade" id="cloneMyRepoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mis repositorios de GitHub</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="myRepoList" class="list-group mb-3 scrollable-list-300">
                    <!-- Se llena con JS -->
                </div>
                <div class="mb-3">
                    <label class="form-label">Carpeta destino:</label>
                    <div class="input-group">
                        <input type="text" id="cloneMyRepoDestino" class="form-control" placeholder="C:\proyectos\destino">
                        <button class="btn btn-outline-secondary" id="browseMyRepoDestinoBtn">
                            <i class="fas fa-folder-open"></i>
                        </button>
                    </div>
                </div>
                <div id="cloneMyRepoError" class="alert alert-danger hidden-element"></div>
                <div id="cloneMyRepoLoading" class="text-center py-2 hidden-element">
                    <i class="fas fa-spinner fa-spin"></i> Clonando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmCloneMyRepoBtn" disabled>Clonar repositorio</button>
            </div>
        </div>
    </div>
</div>