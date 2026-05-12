<?php
declare(strict_types=1);

namespace Core;

class Security
{
    /**
     * Sanitiza una ruta de archivo
     */
    public static function sanitizePath(string $path): string
    {
        $path = strip_tags($path);
        $path = str_replace(['..', ';', '&', '|', '$', '`', '>', '<', '{', '}', '(', ')'], '', $path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $path = trim($path, './ ');
        
        return $path;
    }
    
    /**
     * Valida que una ruta existe y es un directorio
     */
    public static function validateDirectory(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        
        if (is_dir($path)) {
            return true;
        }
        
        $realPath = realpath($path);
        return $realPath !== false && is_dir($realPath);
    }
    
    /**
     * Valida que un archivo está dentro del proyecto (previene path traversal)
     */
    public static function validateFileInProject(string $filePath, string $projectPath): bool
    {
        $projectPath = rtrim(str_replace('\\', '/', $projectPath), '/');
        $fullPath = str_replace('\\', '/', realpath($projectPath . '/' . $filePath));
        
        if ($fullPath === false) {
            return false;
        }
        
        return strpos($fullPath, $projectPath) === 0;
    }
    
    /**
     * Valida una URL de GitHub
     */
    public static function validateGitHubUrl(string $url): bool
    {
        // Patrones permitidos para URLs de GitHub
        $patterns = [
            '/^https:\/\/github\.com\/[\w\-\.]+\/[\w\-\.]+(\.git)?$/i',
            '/^git@github\.com:[\w\-\.]+\/[\w\-\.]+(\.git)?$/i',
            '/^https:\/\/github\.com\/[\w\-\.]+\/[\w\-\.]+$/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitiza output para HTML
     */
    public static function escapeHtml(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valida que un mensaje de commit no contenga comandos
     */
    public static function sanitizeCommitMessage(string $message): string
    {
        // Eliminar caracteres peligrosos del mensaje de commit
        $message = strip_tags($message);
        $message = str_replace([';', '&', '|', '$', '`', '>', '<', '{', '}', '(', ')'], '', $message);
        return trim($message);
    }
    
    /**
     * Lista blanca de comandos Git permitidos
     */
    public static function getAllowedGitCommands(): array
    {
        return [
            'add',
            'commit',
            'push',
            'init',
            'remote',
            'status',
            'clone'
        ];
    }
}