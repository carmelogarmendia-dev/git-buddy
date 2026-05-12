<?php
declare(strict_types=1);

namespace Models;

class FileExplorer
{
    private array $ignoredDirs = [
        '.git', 'node_modules', 'vendor', 'cache', 'logs', 
        'tmp', 'temp', '.idea', '.vscode'
    ];
    
    private array $ignoredFiles = [
        '.DS_Store', 'Thumbs.db', 'desktop.ini'
    ];
    
    private string $projectPath = '';
    private array $gitStatus = [];
    
    public function setProjectPath(string $path): void
    {
        $this->projectPath = rtrim($path, '/');
        $this->loadGitStatus();
    }
    
    private function loadGitStatus(): void
    {
        $this->gitStatus = [];
        
        if (!is_dir($this->projectPath . '/.git')) {
            return;
        }
        
        $command = 'git -C "' . str_replace('/', '\\', $this->projectPath) . '" status --porcelain 2>&1';
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            foreach ($output as $line) {
                if (strlen($line) < 3) {
                    continue;
                }
                
                $statusRaw = substr($line, 0, 2);
                $file = substr($line, 3);
                $file = str_replace('\\', '/', $file);
                $file = trim($file);
                
                // Detectar usando trim() para ignorar espacios
                $statusTrimmed = trim($statusRaw);
                
                if ($statusRaw === '??') {
                    $this->gitStatus[$file] = 'untracked';
                } elseif ($statusTrimmed === 'M') {
                    $this->gitStatus[$file] = 'modified';
                } elseif ($statusTrimmed === 'D') {
                    $this->gitStatus[$file] = 'deleted';
                } elseif ($statusTrimmed === 'A') {
                    $this->gitStatus[$file] = 'added';
                } elseif ($statusTrimmed === 'R') {
                    $this->gitStatus[$file] = 'renamed';
                } else {
                    $this->gitStatus[$file] = 'clean';
                }
            }
        }
    }
    
    public function getFileStatus(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath = trim($relativePath, './');
        
        if (isset($this->gitStatus[$relativePath])) {
            return $this->gitStatus[$relativePath];
        }
        
        // Comprobar si alguna carpeta padre está untracked
        $parts = explode('/', $relativePath);
        $currentPath = '';
        foreach ($parts as $i => $part) {
            if ($i < count($parts) - 1) {
                $currentPath .= ($currentPath ? '/' : '') . $part;
                if (isset($this->gitStatus[$currentPath]) && $this->gitStatus[$currentPath] === 'untracked') {
                    return 'untracked';
                }
            }
        }
        
        return 'clean';
    }
    
    public function scanDirectory(string $path, string $relativePath = ''): array
    {
        $result = [];
        $fullPath = rtrim($path, '/') . '/' . ltrim($relativePath, '/');
        
        if (!is_dir($fullPath)) {
            return $result;
        }
        
        $items = scandir($fullPath);
        if ($items === false) {
            return $result;
        }
        
        foreach ($items as $item) {
            if ($this->shouldIgnore($item, $fullPath . '/' . $item)) {
                continue;
            }
            
            $itemPath = $relativePath ? $relativePath . '/' . $item : $item;
            $fullItemPath = $fullPath . '/' . $item;
            $status = $this->getFileStatus($itemPath);
            
            if (is_dir($fullItemPath)) {
                $children = $this->scanDirectory($path, $itemPath);
                if (!empty($children) || count(scandir($fullItemPath)) > 2 || $status === 'untracked') {
                    $result[] = [
                        'name' => $item,
                        'path' => $itemPath,
                        'type' => 'directory',
                        'status' => $status,
                        'children' => $children
                    ];
                }
            } else {
                $result[] = [
                    'name' => $item,
                    'path' => $itemPath,
                    'type' => 'file',
                    'size' => filesize($fullItemPath),
                    'extension' => pathinfo($item, PATHINFO_EXTENSION),
                    'status' => $status
                ];
            }
        }
        
        usort($result, function($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }
            return ($a['type'] === 'directory') ? -1 : 1;
        });
        
        return $result;
    }
    
    private function shouldIgnore(string $name, string $fullPath): bool
    {
        if ($name === '.' || $name === '..') {
            return true;
        }
        
        if (is_dir($fullPath) && in_array($name, $this->ignoredDirs)) {
            return true;
        }
        
        if (is_file($fullPath) && in_array($name, $this->ignoredFiles)) {
            return true;
        }
        
        return false;
    }
    
    public function initGitRepository(): bool
    {
        if (is_dir($this->projectPath . '/.git')) {
            return true;
        }
        
        $command = 'git -C "' . str_replace('/', '\\', $this->projectPath) . '" init 2>&1';
        exec($command, $output, $returnCode);
        return $returnCode === 0;
    }
    
    public function createGitignoreIfNotExists(): bool
    {
        $gitignorePath = $this->projectPath . '/.gitignore';
        if (file_exists($gitignorePath)) {
            return true;
        }
        
        return file_put_contents($gitignorePath, "# Archivos ignorados por Git Buddy\n") !== false;
    }
    
    public function addToGitignore(string $relativePath): bool
    {
        $gitignorePath = $this->projectPath . '/.gitignore';
        $lineToAdd = str_replace('\\', '/', $relativePath);
        
        $content = file_get_contents($gitignorePath);
        if (strpos($content, $lineToAdd) !== false) {
            return true;
        }
        
        $content .= "\n" . $lineToAdd;
        return file_put_contents($gitignorePath, $content) !== false;
    }
    
    public function getProjectStats(string $path): array
    {
        $totalFiles = 0;
        $totalSize = 0;
        $newFiles = 0;
        $modifiedFiles = 0;
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $shouldIgnore = false;
                foreach ($this->ignoredDirs as $ignoredDir) {
                    if (strpos($file->getPath(), DIRECTORY_SEPARATOR . $ignoredDir . DIRECTORY_SEPARATOR) !== false) {
                        $shouldIgnore = true;
                        break;
                    }
                }
                
                if (!$shouldIgnore && !in_array($file->getFilename(), $this->ignoredFiles)) {
                    $totalFiles++;
                    $totalSize += $file->getSize();
                    
                    $relativePath = str_replace($this->projectPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $status = $this->getFileStatus($relativePath);
                    
                    if ($status === 'untracked') $newFiles++;
                    elseif ($status === 'modified') $modifiedFiles++;
                }
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $this->formatSize($totalSize),
            'new_files' => $newFiles,
            'modified_files' => $modifiedFiles,
            'root_path' => $path
        ];
    }
    
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}