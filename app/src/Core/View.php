<?php
declare(strict_types=1);

namespace Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        ob_start();
        require VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/' . $layout . '.php';
    }
    
    // Nuevo método para renderizar solo contenido (sin layout)
    public static function renderContent(string $view, array $data = []): void
    {
        extract($data);
        require VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
    }
}