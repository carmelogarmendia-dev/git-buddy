<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h3 class="mb-0"><i class="fab fa-github"></i> Configurar GitHub</h3>
                </div>
                <div class="card-body">
                    <div id="tokenStatus"></div>
                    
                    <div id="tokenFormSection">
                        <p class="text-muted mb-4">
                            Para conectar Git Buddy con GitHub, necesitas un <strong>token de acceso personal</strong>.
                            El token se guardará permanentemente en tu equipo.
                        </p>
                        <div class="alert alert-light border mb-4">
                            <h6><i class="fas fa-info-circle"></i> Pasos para generar el token:</h6>
                            <ol class="small mb-0">
                                <li>Ve a <a href="https://github.com/settings/tokens" target="_blank">GitHub Settings → Tokens</a></li>
                                <li>Haz clic en <strong>Generate new token (classic)</strong></li>
                                <li>Selecciona el permiso <strong>repo</strong></li>
                                <li>Copia el token generado</li>
                            </ol>
                        </div>
                        <form id="tokenForm">
                            <div class="mb-3">
                                <label class="form-label">Token de acceso personal:</label>
                                <input type="password" class="form-control token-input" id="token" 
                                       placeholder="ghp_xxxxxxxxxxxxxxxxxxxx" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="saveTokenBtn">
                                <i class="fab fa-github"></i> Guardar token (permanentemente)
                            </button>
                        </form>
                        <div id="errorMsg" class="alert alert-danger mt-3 hidden-element"></div>
                        <div id="loadingMsg" class="alert alert-info mt-3 hidden-element">
                            <i class="fas fa-spinner fa-spin"></i> Verificando token...
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="/" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>