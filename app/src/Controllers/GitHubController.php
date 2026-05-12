<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;
use Core\View;
use Core\Security;
use Models\GitHubApi;

class GitHubController extends Controller
{
    private ?GitHubApi $github = null;
    
    public function __construct()
    {
        if (isset($_SESSION['github_token'])) {
            $this->github = new GitHubApi($_SESSION['github_token']);
        }
    }
    
    public function config(): void
    {
        View::render('github/config', [
            'has_token' => isset($_SESSION['github_token']),
            'username' => $_SESSION['github_username'] ?? null
        ]);
    }
    
    public function saveToken(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['token'] ?? '';
            
            if (empty($token)) {
                $this->jsonResponse(['success' => false, 'error' => 'Token no proporcionado']);
                return;
            }
            
            $this->github = new GitHubApi($token);
            $user = $this->github->getUser();
            
            if (isset($user['token_expired']) && $user['token_expired'] === true) {
                $this->jsonResponse(['success' => false, 'error' => 'Token inválido o expirado', 'token_expired' => true]);
                return;
            }
            
            if (!$user['success']) {
                $this->jsonResponse(['success' => false, 'error' => 'Token inválido: ' . ($user['data']['message'] ?? 'Error desconocido')]);
                return;
            }
            
            $_SESSION['github_token'] = $token;
            $_SESSION['github_username'] = $user['data']['login'];
            $_SESSION['github_token_valid'] = true;
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Token guardado correctamente',
                'username' => $user['data']['login']
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    
    public function logout(): void
    {
        unset($_SESSION['github_token']);
        unset($_SESSION['github_username']);
        unset($_SESSION['github_token_valid']);
        unset($_SESSION['github_remote_configured']);
        $this->jsonResponse(['success' => true]);
    }
    
    public function listRepos(): void
    {
        if (!$this->github && isset($_SESSION['github_token'])) {
            $this->github = new GitHubApi($_SESSION['github_token']);
        }
        
        if (!$this->github) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay token configurado']);
            return;
        }
        
        $repos = $this->github->listRepos();
        
        if (isset($repos['token_expired']) && $repos['token_expired'] === true) {
            unset($_SESSION['github_token']);
            unset($_SESSION['github_username']);
            unset($_SESSION['github_token_valid']);
            $this->jsonResponse([
                'success' => false,
                'error' => 'Token de GitHub expirado o inválido. Reconéctate.',
                'token_expired' => true
            ]);
            return;
        }
        
        if (!$repos['success']) {
            $errorMsg = $repos['data']['message'] ?? 'Error al obtener repositorios';
            $this->jsonResponse(['success' => false, 'error' => $errorMsg]);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'repos' => $repos['data']
        ]);
    }
    
    public function createRepo(): void
    {
        if (!$this->github && isset($_SESSION['github_token'])) {
            $this->github = new GitHubApi($_SESSION['github_token']);
        }
        
        if (!$this->github) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay token configurado']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';
        $private = $input['private'] ?? false;
        
        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'error' => 'Nombre del repositorio requerido']);
            return;
        }
        
        $result = $this->github->createRepo($name, $description, $private);
        
        if (isset($result['token_expired']) && $result['token_expired'] === true) {
            unset($_SESSION['github_token']);
            unset($_SESSION['github_username']);
            $this->jsonResponse(['success' => false, 'error' => 'Token expirado. Reconéctate.', 'token_expired' => true]);
            return;
        }
        
        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Repositorio creado correctamente',
                'repo' => $result['data']
            ]);
        } else {
            $errorMsg = $result['data']['message'] ?? 'Error al crear repositorio';
            $this->jsonResponse(['success' => false, 'error' => $errorMsg]);
        }
    }
    
    public function connectRemote(): void
    {
        if (!isset($_SESSION['current_project'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay proyecto seleccionado']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $repoUrl = $input['repo_url'] ?? '';
        
        if (empty($repoUrl)) {
            $this->jsonResponse(['success' => false, 'error' => 'URL del repositorio requerida']);
            return;
        }
        
        if (!Security::validateGitHubUrl($repoUrl)) {
            $this->jsonResponse(['success' => false, 'error' => 'URL de GitHub inválida']);
            return;
        }
        
        $repoUrl = rtrim($repoUrl, '/');
        if (!str_ends_with($repoUrl, '.git')) {
            $repoUrl .= '.git';
        }
        
        $projectPath = $_SESSION['current_project'];
        
        exec('git -C "' . str_replace('/', '\\', $projectPath) . '" remote get-url origin 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Ya existe un remote origin configurado']);
            return;
        }
        
        $command = 'git -C "' . str_replace('/', '\\', $projectPath) . '" remote add origin ' . $repoUrl . ' 2>&1';
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $_SESSION['github_remote_configured'] = true;
            $this->jsonResponse(['success' => true, 'message' => 'Repositorio remoto conectado correctamente']);
        } else {
            $this->jsonResponse(['success' => false, 'error' => 'Error al conectar remoto: ' . implode("\n", $output)]);
        }
    }
    
    public function getRemoteStatus(): void
    {
        if (!isset($_SESSION['current_project'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay proyecto seleccionado']);
            return;
        }
        
        $projectPath = $_SESSION['current_project'];
        
        exec('git -C "' . str_replace('/', '\\', $projectPath) . '" remote get-url origin 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->jsonResponse([
                'success' => true,
                'configured' => true,
                'remote_url' => $output[0] ?? null
            ]);
        } else {
            $this->jsonResponse([
                'success' => true,
                'configured' => false,
                'remote_url' => null
            ]);
        }
    }
    
    public function clonePublic(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $url = $input['url'] ?? '';
        $destino = $input['destino'] ?? '';
        
        if (empty($url)) {
            $this->jsonResponse(['success' => false, 'error' => 'URL del repositorio requerida']);
            return;
        }
        
        if (empty($destino)) {
            $this->jsonResponse(['success' => false, 'error' => 'Carpeta destino requerida']);
            return;
        }
        
        if (!Security::validateGitHubUrl($url)) {
            $this->jsonResponse(['success' => false, 'error' => 'URL de GitHub inválida']);
            return;
        }
        
        $destino = str_replace('\\', '/', $destino);
        
        $publicPath = realpath(ROOT_PATH . '/public');
        $realDestino = realpath(dirname($destino)); // Validamos el padre ya que el destino puede no existir aún
        
        if ($publicPath && $realDestino && strpos($realDestino, $publicPath) === 0) {
            $this->jsonResponse(['success' => false, 'error' => 'No se puede clonar dentro de la carpeta public/']);
            return;
        }

        
        $parentDir = dirname($destino);
        if (!is_dir($parentDir)) {
            mkdir($parentDir, 0777, true);
        }
        
        if (is_dir($destino)) {
            $files = scandir($destino);
            if (count($files) > 2) {
                $this->jsonResponse(['success' => false, 'error' => 'La carpeta destino no está vacía']);
                return;
            }
            if (count($files) === 2) {
                rmdir($destino);
            }
        }
        
        $command = 'git clone ' . escapeshellarg($url) . ' ' . escapeshellarg($destino) . ' 2>&1';
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->jsonResponse(['success' => true, 'message' => 'Repositorio clonado correctamente', 'path' => $destino]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => implode("\n", $output)]);
        }
    }
    
    public function cloneMyRepo(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $url = $input['url'] ?? '';
        $destino = $input['destino'] ?? '';
        
        if (empty($url)) {
            $this->jsonResponse(['success' => false, 'error' => 'URL del repositorio requerida']);
            return;
        }
        
        if (empty($destino)) {
            $this->jsonResponse(['success' => false, 'error' => 'Carpeta destino requerida']);
            return;
        }
        
        if (!Security::validateGitHubUrl($url)) {
            $this->jsonResponse(['success' => false, 'error' => 'URL de GitHub inválida']);
            return;
        }
        
        $destino = str_replace('\\', '/', $destino);
        
        $publicPath = realpath(ROOT_PATH . '/public');
        $realDestino = realpath(dirname($destino));
        
        if ($publicPath && $realDestino && strpos($realDestino, $publicPath) === 0) {
            $this->jsonResponse(['success' => false, 'error' => 'No se puede clonar dentro de la carpeta public/']);
            return;
        }

        // Añadir token a la URL para repositorios privados
        if (isset($_SESSION['github_token'])) {
            $token = $_SESSION['github_token'];
            $url = str_replace('https://', "https://$token@", $url);
        }

        
        $parentDir = dirname($destino);
        if (!is_dir($parentDir)) {
            mkdir($parentDir, 0777, true);
        }
        
        if (is_dir($destino)) {
            $files = scandir($destino);
            if (count($files) > 2) {
                $this->jsonResponse(['success' => false, 'error' => 'La carpeta destino no está vacía']);
                return;
            }
            if (count($files) === 2) {
                rmdir($destino);
            }
        }
        
        $command = 'git clone ' . escapeshellarg($url) . ' ' . escapeshellarg($destino) . ' 2>&1';
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->jsonResponse(['success' => true, 'message' => 'Repositorio clonado correctamente', 'path' => $destino]);
        } else {
            $this->jsonResponse(['success' => false, 'error' => implode("\n", $output)]);
        }
    }
}