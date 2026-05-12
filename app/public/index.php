<?php
declare(strict_types=1);

// Configuración para producción (ocultar errores)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/php_errors.log');
error_reporting(E_ALL);

session_start();

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PROJECT_ROOT', dirname(ROOT_PATH)); // C:\proyectos\git-buddy
define('VIEWS_PATH', SRC_PATH . '/Views');

// AUTOLOAD MANUAL
spl_autoload_register(function ($class) {
    $map = [
        'Core\\Router' => SRC_PATH . '/Core/Router.php',
        'Core\\Controller' => SRC_PATH . '/Core/Controller.php',
        'Core\\View' => SRC_PATH . '/Core/View.php',
        'Core\\Security' => SRC_PATH . '/Core/Security.php',
        'Controllers\\HomeController' => SRC_PATH . '/Controllers/HomeController.php',
        'Controllers\\ProjectController' => SRC_PATH . '/Controllers/ProjectController.php',
        'Controllers\\GitHubController' => SRC_PATH . '/Controllers/GitHubController.php',
        'Models\\FileExplorer' => SRC_PATH . '/Models/FileExplorer.php',
        'Models\\GitHubApi' => SRC_PATH . '/Models/GitHubApi.php',
    ];
    
    if (isset($map[$class])) {
        require $map[$class];
        return;
    }
    
    $prefixes = [
        'Core\\' => SRC_PATH . '/Core/',
        'Controllers\\' => SRC_PATH . '/Controllers/',
        'Models\\' => SRC_PATH . '/Models/',
    ];
    
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

use Core\Router;

$router = new Router();

// Rutas del proyecto
$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/get-selected-path', 'HomeController@getSelectedPath');

$router->add('POST', '/project/scan', 'ProjectController@scanDirectory');
$router->add('GET', '/project/explorer', 'ProjectController@explorer');
$router->add('GET', '/project/explorer-content', 'ProjectController@getExplorerContent');
$router->add('POST', '/project/gitignore', 'ProjectController@addToGitignore');
$router->add('POST', '/project/refresh', 'ProjectController@refresh');
$router->add('POST', '/project/commit-push', 'ProjectController@commitAndPush');

// Rutas de GitHub
$router->add('GET', '/github/config', 'GitHubController@config');
$router->add('POST', '/github/save-token', 'GitHubController@saveToken');
$router->add('POST', '/github/logout', 'GitHubController@logout');
$router->add('GET', '/github/repos', 'GitHubController@listRepos');
$router->add('POST', '/github/create-repo', 'GitHubController@createRepo');
$router->add('POST', '/github/connect-remote', 'GitHubController@connectRemote');
$router->add('GET', '/github/remote-status', 'GitHubController@getRemoteStatus');
$router->add('POST', '/github/clone-public', 'GitHubController@clonePublic');
$router->add('POST', '/github/clone-my-repo', 'GitHubController@cloneMyRepo');

// Shutdown endpoint (para cierre controlado)
$router->add('POST', '/shutdown', 'HomeController@shutdown');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);