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
    <nav class="navbar navbar-dark bg-dark px-3 mb-3">
        <a href="/" class="navbar-brand"><i class="fas fa-code-branch"></i> Git Buddy</a>
        <div>
            <a href="/github/config" class="btn btn-outline-light btn-sm me-2">
                <i class="fab fa-github"></i> GitHub
            </a>
            <button id="btnCerrarApp" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-power-off"></i> Salir
            </button>
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
