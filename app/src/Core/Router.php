<?php
declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];
    
    public function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch(string $requestMethod, string $requestUri): void
    {
        // Limpiar la URI
        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }
        
        // error_log("Buscando ruta: " . $requestMethod . " " . $requestUri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                // error_log("Ruta encontrada: " . $route['handler']);
                $this->callHandler($route['handler']);
                return;
            }
        }
        
        // error_log("Ruta NO encontrada: " . $requestMethod . " " . $requestUri);
        http_response_code(404);
        echo "404 - Ruta no encontrada: " . $requestMethod . " " . $requestUri;
    }
    
    private function callHandler(string $handler): void
    {
        [$controllerName, $methodName] = explode('@', $handler);
        $controllerClass = "Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            error_log("Clase no encontrada: " . $controllerClass);
            echo "Error: Clase $controllerClass no encontrada";
            return;
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            error_log("Método no encontrado: " . $methodName);
            echo "Error: Método $methodName no encontrado";
            return;
        }
        
        $controller->{$methodName}();
    }
	    public function getRoutes(): array
    {
        return $this->routes;
    }
}