// ============================================
// GIT BUDDY - FUNCIONES GLOBALES
// ============================================

// --------------------------------------------
// 1. SISTEMA DE TOASTS (notificaciones)
// --------------------------------------------
function showToast(message, type = 'info', duration = 5000) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    let icon = '';
    if (type === 'success') icon = '✅';
    else if (type === 'error') icon = '❌';
    else if (type === 'warning') icon = '⚠️';
    else icon = 'ℹ️';
    
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${escapeHtml(message)}</div>
        <div class="toast-close">&times;</div>
    `;
    
    container.appendChild(toast);
    
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    });
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
}

// --------------------------------------------
// 2. FUNCIONES DE UTILIDAD
// --------------------------------------------
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// FUNCIONES DE PROYECTOS RECIENTES
// ============================================
function loadRecentProjects() {
    const recentSection = document.getElementById('recentSection');
    const recentList = document.getElementById('recentList');
    const activeProjectSection = document.getElementById('activeProjectSection');
    const activeProjectCard = document.getElementById('activeProjectCard');
    const emptySessionState = document.getElementById('emptySessionState');
    
    if (!recentSection || !recentList) return;
    
    let hasSomething = false;


    // 1. Mostrar Proyecto Activo (si existe)
    const currentPath = localStorage.getItem('gitbuddy_current_project');
    if (currentPath && activeProjectSection && activeProjectCard) {
        hasSomething = true;
        const projectName = currentPath.split('/').pop() || currentPath.split('\\').pop();
        activeProjectSection.style.display = 'block';
        activeProjectCard.innerHTML = `
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                <div class="overflow-hidden">
                    <h6 class="mb-0 text-truncate">${escapeHtml(projectName)}</h6>
                    <small class="text-muted text-truncate d-block">${escapeHtml(currentPath)}</small>
                </div>
                <div class="ms-3 flex-shrink-0">
                    <button class="btn btn-primary btn-sm mb-1 d-block w-100" onclick="switchTabToExplorer()">
                        <i class="fas fa-external-link-alt"></i> Ver
                    </button>
                    <button class="btn btn-outline-danger btn-sm d-block w-100" onclick="cerrarProyecto()">
                        <i class="fas fa-times"></i> Salir
                    </button>
                </div>
            </div>
        `;
    } else if (activeProjectSection) {
        activeProjectSection.style.display = 'none';
    }

    // 2. Mostrar Proyectos Recientes
    const recent = JSON.parse(localStorage.getItem('gitbuddy_recent') || '[]');
    if (recent.length > 0) {
        hasSomething = true;
        recentSection.style.display = 'block';
        recentList.innerHTML = '';
        recent.forEach((project, index) => {
            const div = document.createElement('div');
            div.className = 'recent-item';
            div.innerHTML = `
                <div class="recent-info">
                    <div class="recent-name">
                        <i class="fas fa-folder text-muted"></i> 
                        ${escapeHtml(project.name)}
                    </div>
                    <div class="recent-path">${escapeHtml(project.path)}</div>
                </div>
                <button class="delete-project-btn" data-index="${index}" title="Eliminar de recientes">
                    <i class="fas fa-times-circle"></i>
                </button>
            `;
            
            const infoDiv = div.querySelector('.recent-info');
            infoDiv.addEventListener('click', () => {
                openProject(project.path);
            });
            
            const deleteBtn = div.querySelector('.delete-project-btn');
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteRecentProject(index);
            });
            
            recentList.appendChild(div);
        });
    } else {
        recentSection.style.display = 'none';
    }

    // 3. Mostrar/Ocultar estado vacío global de la derecha
    if (emptySessionState) {
        emptySessionState.style.display = hasSomething ? 'none' : 'block';
    }
}


function switchTabToExplorer() {
    const explorerTab = document.getElementById('explorer-tab');
    if (explorerTab && !explorerTab.classList.contains('disabled')) {
        const tab = new bootstrap.Tab(explorerTab);
        tab.show();
    }
}

function cerrarProyecto() {
    if (confirm('¿Deseas cerrar el proyecto actual? Esto limpiará la vista del explorador.')) {
        localStorage.removeItem('gitbuddy_current_project');
        
        // Limpiar contenedor del explorador
        const explorerContainer = document.getElementById('explorerContainer');
        if (explorerContainer) {
            explorerContainer.innerHTML = '<div class="text-center p-5"><i class="fas fa-folder-open fa-3x mb-3 text-muted"></i><p class="text-muted">Abre un proyecto para ver su contenido aquí.</p></div>';
        }
        
        // Desactivar pestaña del explorador
        const explorerTab = document.getElementById('explorer-tab');
        if (explorerTab) {
            explorerTab.classList.add('disabled');
            explorerTab.disabled = true;
        }
        
        // Volver a Inicio
        const homeTab = document.getElementById('home-tab');
        if (homeTab) {
            const tab = new bootstrap.Tab(homeTab);
            tab.show();
        }
        
        loadRecentProjects();
        showToast('🚪 Sesión de proyecto cerrada', 'info', 2000);
    }
}


function deleteRecentProject(index) {
    let recent = JSON.parse(localStorage.getItem('gitbuddy_recent') || '[]');
    const removed = recent.splice(index, 1)[0];
    localStorage.setItem('gitbuddy_recent', JSON.stringify(recent));
    
    // Si el proyecto eliminado es el actual, limpiarlo también
    const currentProject = localStorage.getItem('gitbuddy_current_project');
    if (currentProject === removed.path) {
        localStorage.removeItem('gitbuddy_current_project');
        const explorerContainer = document.getElementById('explorerContainer');
        if (explorerContainer) {
            explorerContainer.innerHTML = '<div class="text-center p-5"><i class="fas fa-folder-open fa-3x mb-3 text-muted"></i><p class="text-muted">Abre un proyecto para ver su contenido aquí.</p></div>';
        }
        const explorerTab = document.getElementById('explorer-tab');
        if (explorerTab) {
            explorerTab.classList.add('disabled');
        }
    }
    
    loadRecentProjects();
    showToast(`🗑️ Proyecto "${removed.name}" eliminado de recientes`, 'info', 2000);
}


function saveRecentProject(path) {
    let recent = JSON.parse(localStorage.getItem('gitbuddy_recent') || '[]');
    const name = path.split('/').pop() || path.split('\\').pop();
    const project = { path: path, name: name, timestamp: Date.now() };
    recent = recent.filter(p => p.path !== path);
    recent.unshift(project);
    recent = recent.slice(0, 10);
    localStorage.setItem('gitbuddy_recent', JSON.stringify(recent));
    loadRecentProjects();
}

// ============================================
// FUNCIÓN PARA SELECCIONAR CARPETA (VBS)
// ============================================
async function selectFolderNative() {
    try {
        showToast('📂 Abriendo selector de carpetas de Windows...', 'info', 2000);
        
        await new Promise(resolve => setTimeout(resolve, 100));
        
        const response = await fetch('/get-selected-path');
        const data = await response.json();
        
        console.log('Respuesta del selector:', data);
        
        if (data.success && data.path) {
            return data.path;
        } else {
            showToast('❌ No se seleccionó ninguna carpeta', 'warning');
            return null;
        }
    } catch (error) {
        console.error('Error en selectFolderNative:', error);
        showToast('❌ Error al abrir el selector. Usa la ruta manual.', 'error');
        return null;
    }
}

// ============================================
// GESTIÓN DEL REPOSITORIO REMOTO
// ============================================
async function cargarEstadoRemoto() {
    const container = document.getElementById('remoteStatusContainer');
    if (!container) return;
    
    try {
        const response = await fetch('/github/remote-status');
        const data = await response.json();
        
        if (data.success && data.configured) {
            container.innerHTML = `
                <div class="alert alert-success py-2 mb-0 small">
                    <i class="fas fa-check-circle"></i> Conectado a:<br>
                    <span class="text-break fw-bold">${escapeHtml(data.remote_url)}</span>
                </div>
                <p class="text-muted small mt-2 mb-0">Tus cambios se subirán a esta dirección.</p>
            `;
        } else {
            container.innerHTML = `
                <div class="alert alert-warning py-2 mb-3 small">
                    <i class="fas fa-exclamation-triangle"></i> Sin repositorio remoto configurado.
                </div>
                <div class="input-group input-group-sm">
                    <input type="text" id="remoteUrlInput" class="form-control" placeholder="https://github.com/usuario/repo.git">
                    <button class="btn btn-primary" onclick="conectarRemoto()">Conectar</button>
                </div>
                <small class="text-muted d-block mt-2">Pega la URL de tu repositorio de GitHub arriba.</small>
            `;
        }
    } catch (error) {
        container.innerHTML = '<div class="text-danger small">Error al obtener estado remoto.</div>';
    }
}

async function conectarRemoto() {
    const urlInput = document.getElementById('remoteUrlInput');
    if (!urlInput || !urlInput.value.trim()) {
        showToast('✍️ Por favor, ingresa la URL del repositorio', 'warning');
        return;
    }
    
    const url = urlInput.value.trim();
    showToast('🔗 Conectando repositorio...', 'info');
    
    try {
        const response = await fetch('/github/connect-remote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ repo_url: url })
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('✅ Repositorio remoto conectado', 'success');
            cargarEstadoRemoto();
        } else {
            showToast('❌ Error: ' + (data.error || 'No se pudo conectar'), 'error');
        }
    } catch (error) {
        showToast('❌ Error de conexión', 'error');
    }
}


// ============================================
// CARGAR EXPLORADOR DENTRO DEL TAB
// ============================================

async function cargarExploradorEnTab(switchTab = true) {
    const explorerContainer = document.getElementById('explorerContainer');
    if (!explorerContainer) return;
    
    const currentProject = localStorage.getItem('gitbuddy_current_project');
    if (!currentProject) {
        explorerContainer.innerHTML = '<div class="text-center p-5"><i class="fas fa-folder-open fa-3x mb-3 text-muted"></i><p class="text-muted">Abre un proyecto para ver su contenido aquí.</p></div>';
        return;
    }
    
    explorerContainer.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-3">Cargando explorador...</p></div>';
    
    try {
        // Cargar el contenido del explorador desde el servidor
        const response = await fetch('/project/explorer-content');
        const html = await response.text();
        explorerContainer.innerHTML = html;
        
        // Reinicializar los eventos del explorador (checkboxes, etc.)
        iniciarEventosExplorador();
        
        // Activar el tab Explorador
        const explorerTab = document.getElementById('explorer-tab');
        if (explorerTab) {
            explorerTab.classList.remove('disabled');
            explorerTab.disabled = false;
            
            // Cambiar al tab Explorador solo si switchTab es true
            if (switchTab) {
                const tab = new bootstrap.Tab(explorerTab);
                tab.show();
            }
        }
    } catch (error) {
        console.error('Error cargando explorador:', error);
        explorerContainer.innerHTML = '<div class="text-center p-5 text-danger">Error al cargar el explorador.</div>';
    }
}


// ============================================
// FUNCIÓN PARA ABRIR PROYECTO
// ============================================
async function openProject(path) {
    if (!path) return;
    
    // Guardar proyecto actual
    localStorage.setItem('gitbuddy_current_project', path);
    
    const errorDiv = document.getElementById('errorMsg');
    const loadingDiv = document.getElementById('loadingMsg');
    
    if (errorDiv) errorDiv.style.display = 'none';
    if (loadingDiv) loadingDiv.style.display = 'block';
    
    try {
        console.log('Llamando a /project/scan con path:', path);
        const response = await fetch('/project/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: path })
        });
        
        const text = await response.text();
        console.log('Respuesta RAW de /project/scan:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch(e) {
            console.error('Error parseando JSON:', e);
            showToast('❌ Error en respuesta del servidor', 'error');
            if (loadingDiv) loadingDiv.style.display = 'none';
            return;
        }
        
        if (data.success) {
            saveRecentProject(path);
            // En lugar de redirigir, cargar el explorador en el tab
            cargarExploradorEnTab();
        } else {
            showToast(`❌ ${data.error || 'Error al cargar el proyecto'}`, 'error');
        }
    } catch (error) {
        console.error('Error en fetch:', error);
        showToast(`❌ Error de conexión: ${error.message}`, 'error');
    } finally {
        if (loadingDiv) loadingDiv.style.display = 'none';
    }
}

// ============================================
// FUNCIÓN DE CIERRE DE APLICACIÓN
// ============================================
function cerrarAplicacion() {
    if (confirm('¿Cerrar Git Buddy?')) {
        // 1. Avisar al servidor para que limpie y se apague (usamos keepalive para que la petición no se corte al cerrar la ventana)
        fetch('/shutdown', { method: 'POST', keepalive: true }).catch(() => {});
        
        // 2. Cerrar la ventana inmediatamente
        // En modo aplicación de Chrome/Edge, esto funciona de forma limpia.
        window.close();
    }
}

// ============================================
// FUNCIÓN PARA INICIALIZAR EVENTOS DEL EXPLORADOR
// (se llama después de cargar el explorador dinámicamente)
// ============================================
function iniciarEventosExplorador() {
    console.log('Eventos del explorador inicializados');
    
    // 1. Toggle de carpetas
    document.querySelectorAll('.toggle-dir').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const targetId = btn.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            
            if (target.style.display === 'none' || target.classList.contains('hidden-element')) {
                target.style.display = 'block';
                target.classList.remove('hidden-element');
                icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
            } else {
                target.style.display = 'none';
                target.classList.add('hidden-element');
                icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
            }
        });
    });
    
    // 2. Selección de checkboxes
    const selectAll = document.getElementById('selectAll');
    const fileCheckboxes = document.querySelectorAll('.file-checkbox');
    const dirCheckboxes = document.querySelectorAll('.directory-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const commitBtn = document.getElementById('commitBtn');
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.file-checkbox:checked').length;
        if (selectedCount) selectedCount.textContent = checked;
        if (commitBtn) commitBtn.disabled = false; // Habilitar siempre que haya un proyecto abierto (o según lógica deseada)
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            fileCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelectedCount();
        });
    }
    
    fileCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    dirCheckboxes.forEach(dirCb => {
        dirCb.addEventListener('change', () => {
            const parent = dirCb.closest('.directory');
            const childrenCbs = parent.querySelectorAll('.file-checkbox');
            childrenCbs.forEach(cb => cb.checked = dirCb.checked);
            updateSelectedCount();
        });
    });
    
    // 3. Botón Refrescar
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            cargarExploradorEnTab();
            showToast('🔄 Estado del repositorio actualizado', 'success', 2000);
        });
    }

    // 3.5 Cargar Estado Remoto (NUEVO)
    cargarEstadoRemoto();

    // 4. Botón Commit & Push

    if (commitBtn) {
        commitBtn.addEventListener('click', async () => {
            const message = document.getElementById('commitMessage').value.trim();
            if (!message) {
                showToast('✍️ Por favor, escribe un mensaje para el commit', 'warning');
                return;
            }
            
            const selectedFiles = Array.from(document.querySelectorAll('.file-checkbox:checked')).map(cb => cb.value);
            
            commitBtn.disabled = true;
            commitBtn.classList.add('committing');
            commitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo a GitHub...';
            
            try {
                const response = await fetch('/project/commit-push', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: message,
                        files: selectedFiles
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('🚀 ¡Cambios subidos correctamente!', 'success');
                    document.getElementById('commitMessage').value = '';
                    cargarExploradorEnTab();
                } else {
                    showToast('❌ Error: ' + (data.error || 'No se pudieron subir los cambios'), 'error');
                }
            } catch (error) {
                showToast('❌ Error de conexión al subir cambios', 'error');
            } finally {
                commitBtn.disabled = false;
                commitBtn.classList.remove('committing');
                commitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Commit y Push a GitHub';
            }
        });
    }
    
    // 5. Menú Contextual (.gitignore)
    const contextMenu = document.getElementById('contextMenu');
    let fileToIgnore = null;
    
    document.querySelectorAll('.file-item.file').forEach(item => {
        item.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            fileToIgnore = item.getAttribute('data-file-path');
            
            contextMenu.style.top = `${e.clientY}px`;
            contextMenu.style.left = `${e.clientX}px`;
            contextMenu.classList.remove('context-menu-hidden');
            contextMenu.style.display = 'block';
        });
    });
    
    document.addEventListener('click', () => {
        if (contextMenu) {
            contextMenu.style.display = 'none';
            contextMenu.classList.add('context-menu-hidden');
        }
    });
    
    const ignoreFileBtn = document.getElementById('ignoreFileBtn');
    if (ignoreFileBtn) {
        ignoreFileBtn.addEventListener('click', async () => {
            if (!fileToIgnore) return;
            
            try {
                const response = await fetch('/project/gitignore', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ file: fileToIgnore })
                });
                const data = await response.json();
                if (data.success) {
                    showToast(`🚫 Archivo ignorado: ${fileToIgnore}`, 'success');
                    cargarExploradorEnTab();
                }
            } catch (e) {
                showToast('❌ Error al ignorar archivo', 'error');
            }
        });
    }

    // 6. Ayuda Tooltip
    const helpBtn = document.getElementById('helpBtn');
    const helpTooltip = document.getElementById('helpTooltip');
    if (helpBtn && helpTooltip) {
        helpBtn.addEventListener('mouseenter', () => helpTooltip.style.display = 'block');
        helpBtn.addEventListener('mouseleave', () => helpTooltip.style.display = 'none');
    }

    updateSelectedCount();
}


// ============================================
// INICIALIZACIÓN AL CARGAR EL DOM
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Git Buddy JS cargado correctamente');
    
    // Recuperar proyecto actual si existe (pero no cambiar de pestaña automáticamente)
    const currentProject = localStorage.getItem('gitbuddy_current_project');
    if (currentProject) {
        // Solo precargar los datos pero no cambiar el tab
        cargarExploradorEnTab(false); 
    }

    
    // --------------------------------------------
    // Verificar si hay un proyecto clonado pendiente
    // --------------------------------------------
    const clonedProject = localStorage.getItem('gitbuddy_cloned_project');
    if (clonedProject) {
        localStorage.removeItem('gitbuddy_cloned_project');
        setTimeout(() => {
            if (confirm('Repositorio clonado correctamente. ¿Abrir el proyecto ahora?')) {
                openProject(clonedProject);
            }
        }, 500);
    }
    
    // --------------------------------------------
    // PÁGINA DE INICIO (HOME)
    // --------------------------------------------
    const folderSelector = document.getElementById('folderSelector');
    const manualPath = document.getElementById('manualPath');
    const useManualBtn = document.getElementById('useManualBtn');
    
    async function selectFolderForProject() {
        const path = await selectFolderNative();
        if (path) {
            manualPath.value = path;
            showToast('✅ Ruta encontrada: ' + path, 'success', 3000);
            openProject(path);
        }
    }
    
    if (folderSelector) {
        folderSelector.addEventListener('click', selectFolderForProject);
        
        folderSelector.addEventListener('dragover', (e) => {
            e.preventDefault();
            folderSelector.classList.add('dragover');
        });
        
        folderSelector.addEventListener('dragleave', () => {
            folderSelector.classList.remove('dragover');
        });
        
        folderSelector.addEventListener('drop', (e) => {
            e.preventDefault();
            folderSelector.classList.remove('dragover');
            showToast('📂 Para seleccionar una carpeta, haz clic en el recuadro.\n\nEl arrastre de carpetas estará disponible en una próxima actualización.', 'info', 5000);
        });
    }
    
    if (useManualBtn) {
        useManualBtn.addEventListener('click', () => {
            const path = manualPath.value.trim();
            if (!path) {
                showToast('📁 Por favor, ingresa una ruta válida', 'warning');
                return;
            }
            openProject(path);
        });
    }
    
    if (manualPath) {
        manualPath.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && useManualBtn) {
                useManualBtn.click();
            }
        });
    }
    
    loadRecentProjects();
    
    // --------------------------------------------
    // CLONAR REPOSITORIO PÚBLICO - Selector destino
    // --------------------------------------------
    const browseDestinoBtn = document.getElementById('browseDestinoBtn');
    if (browseDestinoBtn) {
        browseDestinoBtn.addEventListener('click', async () => {
            const path = await selectFolderNative();
            if (path) {
                document.getElementById('cloneDestino').value = path;
                showToast('✅ Carpeta destino seleccionada: ' + path, 'success', 3000);
            }
        });
    }
    
    // --------------------------------------------
    // CLONAR MIS REPOSITORIOS - Selector destino
    // --------------------------------------------
    const browseMyRepoDestinoBtn = document.getElementById('browseMyRepoDestinoBtn');
    if (browseMyRepoDestinoBtn) {
        browseMyRepoDestinoBtn.addEventListener('click', async () => {
            const path = await selectFolderNative();
            if (path) {
                document.getElementById('cloneMyRepoDestino').value = path;
                showToast('✅ Carpeta destino seleccionada: ' + path, 'success', 3000);
            }
        });
    }
    
    // --------------------------------------------
    // CLONAR REPOSITORIO PÚBLICO - Confirmar
    // --------------------------------------------
    const confirmCloneBtn = document.getElementById('confirmCloneBtn');
    if (confirmCloneBtn) {
        confirmCloneBtn.addEventListener('click', async () => {
            const url = document.getElementById('cloneUrl')?.value.trim();
            const destino = document.getElementById('cloneDestino')?.value.trim();
            
            const cloneError = document.getElementById('cloneError');
            const cloneLoading = document.getElementById('cloneLoading');
            
            if (cloneError) cloneError.style.display = 'none';
            if (cloneLoading) cloneLoading.style.display = 'block';
            confirmCloneBtn.disabled = true;
            
            if (!url) {
                showToast('📦 Por favor, ingresa la URL del repositorio', 'warning');
                if (cloneLoading) cloneLoading.style.display = 'none';
                confirmCloneBtn.disabled = false;
                return;
            }
            
            if (!destino) {
                showToast('📁 Por favor, ingresa la carpeta destino', 'warning');
                if (cloneLoading) cloneLoading.style.display = 'none';
                confirmCloneBtn.disabled = false;
                return;
            }
            
            try {
                console.log('Clonando repositorio:', url, 'en', destino);
                const response = await fetch('/github/clone-public', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: url, destino: destino })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('✅ Repositorio clonado correctamente', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('cloneModal'));
                    if (modal) modal.hide();
                    
                    localStorage.setItem('gitbuddy_cloned_project', destino);
                    window.location.href = '/';
                } else {
                    showToast(`❌ ${data.error || 'Error al clonar el repositorio'}`, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(`❌ Error de conexión: ${error.message}`, 'error');
            } finally {
                if (cloneLoading) cloneLoading.style.display = 'none';
                confirmCloneBtn.disabled = false;
            }
        });
    }
    
    // --------------------------------------------
    // CLONAR MIS REPOSITORIOS - Cargar lista y confirmar
    // --------------------------------------------
    let selectedMyRepo = null;
    
    const cloneMyRepoBtn = document.getElementById('cloneMyRepoBtn');
    if (cloneMyRepoBtn) {
        cloneMyRepoBtn.addEventListener('click', async () => {
            const repoList = document.getElementById('myRepoList');
            if (repoList) {
                repoList.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Cargando repositorios...</div>';
            }
            selectedMyRepo = null;
            const confirmBtn = document.getElementById('confirmCloneMyRepoBtn');
            if (confirmBtn) confirmBtn.disabled = true;
            
            try {
                const response = await fetch('/github/repos');
                const data = await response.json();
                
                if (data.token_expired) {
                    localStorage.removeItem('github_token');
                    showToast('🔑 Tu token de GitHub ha expirado. Por favor, reconéctate.', 'warning');
                    setTimeout(() => {
                        window.location.href = '/github/config';
                    }, 3000);
                    return;
                }
                
                if (data.success && data.repos && Array.isArray(data.repos) && data.repos.length > 0) {
                    if (repoList) repoList.innerHTML = '';
                    data.repos.forEach(repo => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${escapeHtml(repo.name)}</strong>
                                    <br>
                                    <small class="text-muted">${repo.private ? '🔒 Privado' : '🌍 Público'}</small>
                                </div>
                                <small>${repo.updated_at ? new Date(repo.updated_at).toLocaleDateString() : ''}</small>
                            </div>
                            <div class="small text-muted">${repo.description ? escapeHtml(repo.description) : 'Sin descripción'}</div>
                        `;
                        btn.addEventListener('click', () => {
                            document.querySelectorAll('#myRepoList .list-group-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            btn.classList.add('active');
                            selectedMyRepo = repo;
                            if (confirmBtn) confirmBtn.disabled = false;
                            
                            const destinoInput = document.getElementById('cloneMyRepoDestino');
                            if (destinoInput && !destinoInput.value) {
                                destinoInput.value = 'C:/laragon/www/' + repo.name;
                            }
                        });
                        if (repoList) repoList.appendChild(btn);
                    });
                } else {
                    if (repoList) {
                        repoList.innerHTML = '<div class="text-center p-3 text-muted">No tienes repositorios o no estás conectado a GitHub. Ve a "GitHub" y conecta tu token.</div>';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (repoList) {
                    repoList.innerHTML = '<div class="text-center p-3 text-danger">Error al cargar repositorios. Asegúrate de estar conectado a GitHub.</div>';
                }
            }
        });
    }
    
    const confirmCloneMyRepoBtn = document.getElementById('confirmCloneMyRepoBtn');
    if (confirmCloneMyRepoBtn) {
        confirmCloneMyRepoBtn.addEventListener('click', async () => {
            if (!selectedMyRepo) return;
            
            const destino = document.getElementById('cloneMyRepoDestino')?.value.trim();
            const cloneUrl = selectedMyRepo.clone_url;
            
            const errorDivClone = document.getElementById('cloneMyRepoError');
            const loadingDivClone = document.getElementById('cloneMyRepoLoading');
            
            if (errorDivClone) errorDivClone.style.display = 'none';
            if (loadingDivClone) loadingDivClone.style.display = 'block';
            confirmCloneMyRepoBtn.disabled = true;
            
            try {
                console.log('Clonando repositorio propio:', cloneUrl, 'en', destino);
                const response = await fetch('/github/clone-my-repo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: cloneUrl, destino: destino })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('✅ Repositorio clonado correctamente', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('cloneMyRepoModal'));
                    if (modal) modal.hide();
                    
                    localStorage.setItem('gitbuddy_cloned_project', destino);
                    window.location.href = '/';
                } else {
                    showToast(`❌ ${data.error || 'Error al clonar'}`, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(`❌ Error de conexión: ${error.message}`, 'error');
            } finally {
                if (loadingDivClone) loadingDivClone.style.display = 'none';
                confirmCloneMyRepoBtn.disabled = false;
            }
        });
    }
    
    // --------------------------------------------
    // LIMPIAR MODALES AL CERRAR
    // --------------------------------------------
    const cloneModal = document.getElementById('cloneModal');
    if (cloneModal) {
        cloneModal.addEventListener('hidden.bs.modal', () => {
            const cloneUrl = document.getElementById('cloneUrl');
            const cloneDestino = document.getElementById('cloneDestino');
            const cloneError = document.getElementById('cloneError');
            const cloneLoading = document.getElementById('cloneLoading');
            if (cloneUrl) cloneUrl.value = '';
            if (cloneDestino) cloneDestino.value = '';
            if (cloneError) cloneError.style.display = 'none';
            if (cloneLoading) cloneLoading.style.display = 'none';
        });
    }
    
    const cloneMyRepoModal = document.getElementById('cloneMyRepoModal');
    if (cloneMyRepoModal) {
        cloneMyRepoModal.addEventListener('hidden.bs.modal', () => {
            const destino = document.getElementById('cloneMyRepoDestino');
            const errorDivClone = document.getElementById('cloneMyRepoError');
            const loadingDivClone = document.getElementById('cloneMyRepoLoading');
            if (destino) destino.value = '';
            if (errorDivClone) errorDivClone.style.display = 'none';
            if (loadingDivClone) loadingDivClone.style.display = 'none';
            selectedMyRepo = null;
            const confirmBtn = document.getElementById('confirmCloneMyRepoBtn');
            if (confirmBtn) confirmBtn.disabled = true;
        });
    }
    
    // --------------------------------------------
    // PÁGINA DE CONFIGURACIÓN GITHUB
    // --------------------------------------------
    if (document.getElementById('tokenForm')) {
        (function() {
            function loadTokenFromStorage() {
                const token = localStorage.getItem('github_token');
                const tokenInput = document.getElementById('token');
                if (token && tokenInput) {
                    tokenInput.value = token;
                    verifyToken(token);
                }
            }
            
            async function verifyToken(token) {
                const statusDiv = document.getElementById('tokenStatus');
                if (!statusDiv) return;
                
                statusDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Verificando token...</div>';
                
                try {
                    const response = await fetch('/github/save-token', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: token })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        statusDiv.innerHTML = `
                            <div class="alert success-card text-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                                <p class="mt-2 mb-0">✅ Conectado como <strong>${escapeHtml(data.username)}</strong></p>
                                <p class="small mt-1">Token guardado permanentemente</p>
                            </div>
                        `;
                        const formSection = document.getElementById('tokenFormSection');
                        if (formSection) formSection.style.display = 'none';
                        showToast('✅ Token guardado y verificado correctamente', 'success');
                    } else if (data.token_expired) {
                        statusDiv.innerHTML = `
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                                <p class="mt-2 mb-0">⚠️ El token ha expirado o es inválido</p>
                                <p class="small">Por favor, genera un nuevo token en GitHub</p>
                            </div>
                        `;
                        localStorage.removeItem('github_token');
                        const formSection = document.getElementById('tokenFormSection');
                        if (formSection) formSection.style.display = 'block';
                    } else {
                        statusDiv.innerHTML = `
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-times-circle fa-2x"></i>
                                <p class="mt-2 mb-0">❌ Token inválido</p>
                                <p class="small">${escapeHtml(data.error || 'Verifica que el token tenga permiso "repo"')}</p>
                            </div>
                        `;
                        localStorage.removeItem('github_token');
                        const formSection = document.getElementById('tokenFormSection');
                        if (formSection) formSection.style.display = 'block';
                    }
                } catch (error) {
                    statusDiv.innerHTML = `<div class="alert alert-danger">Error de conexión: ${escapeHtml(error.message)}</div>`;
                }
            }
            
            async function saveTokenToBackend(token) {
                try {
                    const response = await fetch('/github/save-token', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: token })
                    });
                    return await response.json();
                } catch (error) {
                    return { success: false, error: error.message };
                }
            }
            
            const form = document.getElementById('tokenForm');
            const tokenInput = document.getElementById('token');
            const errorDiv = document.getElementById('errorMsg');
            const loadingDiv = document.getElementById('loadingMsg');
            const saveTokenBtn = document.getElementById('saveTokenBtn');
            
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const token = tokenInput.value.trim();
                    if (!token) {
                        showToast('Por favor, ingresa un token válido', 'warning');
                        return;
                    }
                    
                    if (errorDiv) errorDiv.style.display = 'none';
                    if (loadingDiv) loadingDiv.style.display = 'block';
                    if (saveTokenBtn) saveTokenBtn.disabled = true;
                    
                    try {
                        const response = await saveTokenToBackend(token);
                        
                        if (response.success) {
                            localStorage.setItem('github_token', token);
                            showToast(`✅ Conectado como ${response.username}. Token guardado permanentemente.`, 'success');
                            setTimeout(() => {
                                window.location.href = '/github/config';
                            }, 1500);
                        } else if (response.token_expired) {
                            showToast('❌ Token inválido o expirado. Genera uno nuevo en GitHub.', 'error');
                            if (errorDiv) {
                                errorDiv.textContent = 'Token inválido o expirado. Genera uno nuevo en GitHub.';
                                errorDiv.style.display = 'block';
                            }
                        } else {
                            showToast(`❌ ${response.error || 'Error al guardar el token'}`, 'error');
                            if (errorDiv) {
                                errorDiv.textContent = response.error || 'Error al guardar el token';
                                errorDiv.style.display = 'block';
                            }
                        }
                    } catch (error) {
                        showToast(`❌ Error de conexión: ${error.message}`, 'error');
                        if (errorDiv) {
                            errorDiv.textContent = 'Error de conexión: ' + error.message;
                            errorDiv.style.display = 'block';
                        }
                    } finally {
                        if (loadingDiv) loadingDiv.style.display = 'none';
                        if (saveTokenBtn) saveTokenBtn.disabled = false;
                    }
                });
            }
            
            loadTokenFromStorage();
        })();
    }
});

// ============================================
// EVENTO GLOBAL PARA EL BOTÓN DE CIERRE
// ============================================
document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('#btnCerrarApp');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        cerrarAplicacion();
    }
});