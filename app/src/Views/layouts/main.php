<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Git Buddy</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Git Buddy CSS -->
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="<?= $bodyClass ?? '' ?>">
    
    <!-- BARRA SUPERIOR (Unificada) -->
    <nav class="navbar navbar-dark bg-dark px-3 mb-0 shadow-sm">
        <div class="container-fluid">
            <a href="/" class="navbar-brand fw-bold">
                <i class="fas fa-code-branch text-primary"></i> Git Buddy
            </a>
            
            <!-- Acciones de Proyecto (Dinámicas) -->
            <div id="projectNavbarActions" class="d-flex align-items-center gap-2 hidden-element">
                <span class="badge bg-secondary me-2 d-none d-md-inline" id="navProjectName"></span>
                <button class="btn btn-outline-light btn-sm" id="navRefreshBtn">
                    <i class="fas fa-sync-alt"></i> <span class="d-none d-sm-inline">Refrescar</span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <a href="/github/config" class="btn btn-link text-light text-decoration-none me-3">
                    <i class="fab fa-github"></i> <span class="d-none d-md-inline">GitHub</span>
                </a>
                <button id="btnCerrarApp" class="btn btn-danger btn-sm px-3">
                    <i class="fas fa-power-off"></i> <span class="d-none d-sm-inline">Cerrar</span>
                </button>
            </div>
        </div>
    </nav>

    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Contenido principal -->
    <main class="container-fluid px-4">
        <?= $content ?>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Git Buddy JS -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
