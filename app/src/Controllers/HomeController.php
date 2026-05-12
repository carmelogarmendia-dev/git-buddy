<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\View;

class HomeController extends Controller
{
    public function index(): void
    {
        View::render('home/index', [
            'message' => 'Git Buddy funciona correctamente!'
        ]);
    }
    
    /**
     * Endpoint para solicitar el cierre controlado del servidor
     * Marca el lock file como "shutting-down" para que el VBS sepa cuándo cerrar
     */
    public function shutdown(): void
    {
        // Ruta al lock file (directorio raíz del proyecto)
        $lockFile = PROJECT_ROOT . '/gitbuddy.lock';
        
        // Marcar que se quiere cerrar (no eliminar)
        if (file_exists($lockFile)) {
            file_put_contents($lockFile, "shutting-down");
        }
        
        // Devolver respuesta exitosa
        $this->jsonResponse([
            'success' => true,
            'message' => 'Servidor se cerrará cuando cierres la ventana'
        ]);
    }

    /**
     * Lanza el script VBS para seleccionar una carpeta en Windows
     */
    public function getSelectedPath(): void
    {
        $vbsScript = PROJECT_ROOT . '/select_folder.vbs';
        $outputPath = PROJECT_ROOT . '/selected_path.txt';

        // 1. Eliminar archivo de salida anterior si existe
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        // 2. Ejecutar el script VBS de forma síncrona
        $cmd = 'cscript //nologo "' . $vbsScript . '"';
        exec($cmd);

        // 3. Esperar y leer el resultado
        if (file_exists($outputPath)) {
            $path = trim(file_get_contents($outputPath));
            unlink($outputPath); // Limpiar

            if ($path === 'CANCEL' || empty($path)) {
                $this->jsonResponse(['success' => false, 'error' => 'Cancelado']);
            } else {
                $this->jsonResponse(['success' => true, 'path' => $path]);
            }
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'No se pudo obtener la ruta']);
        }
    }
}