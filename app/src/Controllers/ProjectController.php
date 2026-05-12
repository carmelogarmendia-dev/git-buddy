<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\View;
use Core\Security;
use Models\FileExplorer;

class ProjectController extends Controller
{
    private FileExplorer $fileExplorer;
    
    public function __construct()
    {
        $this->fileExplorer = new FileExplorer();
    }
    
    public function scanDirectory(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new \Exception('No se recibieron datos JSON válidos');
            }
            
            $path = $input['path'] ?? '';
            if (empty($path)) {
                throw new \Exception('No se proporcionó ninguna ruta');
            }
            
            $path = Security::sanitizePath($path);
            
            if (!Security::validateDirectory($path)) {
                throw new \Exception('La ruta no existe o no es un directorio: ' . $path);
            }
            
            $this->fileExplorer->setProjectPath($path);
            
            $this->fileExplorer->initGitRepository();
            $this->fileExplorer->createGitignoreIfNotExists();
            
            $_SESSION['current_project'] = $path;
            $_SESSION['project_structure'] = $this->fileExplorer->scanDirectory($path);
            $_SESSION['project_stats'] = $this->fileExplorer->getProjectStats($path);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Proyecto cargado correctamente',
                'stats' => $_SESSION['project_stats']
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function explorer(): void
    {
        if (!isset($_SESSION['current_project'])) {
            header('Location: /');
            exit;
        }
        
        View::render('project/explorer', [
            'project_path' => $_SESSION['current_project'],
            'stats' => $_SESSION['project_stats'] ?? [],
            'structure' => $_SESSION['project_structure'] ?? []
        ]);
    }
    
    // NUEVO MÉTODO: Devuelve solo el contenido HTML del explorador (sin layout)
    public function getExplorerContent(): void
    {
        if (!isset($_SESSION['current_project'])) {
            http_response_code(400);
            echo "No hay proyecto seleccionado";
            return;
        }
        
        $path = $_SESSION['current_project'];
        
        try {
            $this->fileExplorer->setProjectPath($path);
            $_SESSION['project_structure'] = $this->fileExplorer->scanDirectory($path);
            $_SESSION['project_stats'] = $this->fileExplorer->getProjectStats($path);
        } catch (\Exception $e) {
            // Si falla el re-escaneo, usamos lo que tengamos en sesión
        }
        
        View::renderContent('project/explorer-content', [
            'project_path' => $path,
            'stats' => $_SESSION['project_stats'] ?? [],
            'structure' => $_SESSION['project_structure'] ?? []
        ]);
    }

    
    public function addToGitignore(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $filePath = $input['file'] ?? '';
        
        if (!isset($_SESSION['current_project'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay proyecto seleccionado']);
            return;
        }
        
        $this->fileExplorer->setProjectPath($_SESSION['current_project']);
        $result = $this->fileExplorer->addToGitignore($filePath);
        
        if ($result) {
            $_SESSION['project_structure'] = $this->fileExplorer->scanDirectory($_SESSION['current_project']);
            $_SESSION['project_stats'] = $this->fileExplorer->getProjectStats($_SESSION['current_project']);
        }
        
        $this->jsonResponse([
            'success' => $result,
            'message' => $result ? 'Archivo añadido a .gitignore' : 'Error al añadir a .gitignore'
        ]);
    }
    
    public function refresh(): void
    {
        if (!isset($_SESSION['current_project'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay proyecto seleccionado']);
            return;
        }
        
        $path = $_SESSION['current_project'];
        $this->fileExplorer->setProjectPath($path);
        
        $_SESSION['project_structure'] = $this->fileExplorer->scanDirectory($path);
        $_SESSION['project_stats'] = $this->fileExplorer->getProjectStats($path);
        
        $this->jsonResponse(['success' => true]);
    }
    
    public function getFiles(): void
    {
        if (!isset($_SESSION['current_project'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay proyecto seleccionado']);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'structure' => $_SESSION['project_structure'] ?? []
        ]);
    }
    
    public function commitAndPush(): void
    {
        if (!isset($_SESSION['current_project'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay proyecto seleccionado']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $files = $input['files'] ?? [];
        $message = $input['message'] ?? '';
        
        if (empty($message)) {

            $this->jsonResponse(['success' => false, 'error' => 'Mensaje de commit requerido']);
            return;
        }
        
        $message = Security::sanitizeCommitMessage($message);
        if (empty($message)) {
            $this->jsonResponse(['success' => false, 'error' => 'Mensaje de commit inválido']);
            return;
        }
        
        $projectPath = $_SESSION['current_project'];
        $errors = [];
        
        if (empty($files)) {
            // Si no hay archivos seleccionados, añadimos TODO (.)
            $command = 'git -C "' . str_replace('/', '\\', $projectPath) . '" add . 2>&1';
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                $errors[] = "Error al añadir todos los archivos: " . implode("\n", $output);
            }
        } else {
            // Si hay selección, añadimos solo esos
            foreach ($files as $file) {
                if (!Security::validateFileInProject($file, $projectPath)) {
                    $errors[] = "Archivo inválido: $file";
                    continue;
                }
                
                $file = escapeshellarg($file);
                $command = 'git -C "' . str_replace('/', '\\', $projectPath) . '" add ' . $file . ' 2>&1';
                exec($command, $output, $returnCode);
                if ($returnCode !== 0) {
                    $errors[] = "Error al añadir $file: " . implode("\n", $output);
                }
            }
        }

        
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'error' => implode("\n", $errors)]);
            return;
        }
        
        $message = escapeshellarg($message);
        $command = 'git -C "' . str_replace('/', '\\', $projectPath) . '" commit -m ' . $message . ' 2>&1';
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Error al hacer commit: ' . implode("\n", $output)]);
            return;
        }
        
        $command = 'git -C "' . str_replace('/', '\\', $projectPath) . '" push -u origin main 2>&1';
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $command = 'git -C "' . str_replace('/', '\\', $projectPath) . '" push -u origin master 2>&1';
            exec($command, $output, $returnCode);
        }
        
        if ($returnCode !== 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Error al hacer push: ' . implode("\n", $output)]);
            return;
        }
        
        $this->fileExplorer->setProjectPath($projectPath);
        $_SESSION['project_structure'] = $this->fileExplorer->scanDirectory($projectPath);
        $_SESSION['project_stats'] = $this->fileExplorer->getProjectStats($projectPath);
        
        $this->jsonResponse(['success' => true, 'message' => 'Commit y push completados correctamente']);
    }
}