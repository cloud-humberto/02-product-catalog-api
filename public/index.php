<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

// Autoload simples para Api\*
spl_autoload_register(function ($class) {
    $prefix = 'Api\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use Api\Middleware\AuthMiddleware;
use Api\Router;

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Se acessar a raiz ou /docs, exibe a interface interativa de documentação
if ($uri === '/' || $uri === '/docs') {
    require_once __DIR__ . '/docs.html';
    exit;
}

$router = new Router();

// Rotas de Autenticação
$router->post('/api/v1/auth/login', 'AuthController@login');
$router->post('/api/v1/auth/register', 'AuthController@register');

// Rotas de Produtos
$router->get('/api/v1/products', 'ProductController@index');
$router->get('/api/v1/products/{id}', 'ProductController@show');

// Rotas protegidas por Bearer Token
$router->post('/api/v1/products', 'ProductController@store', [AuthMiddleware::class]);
$router->put('/api/v1/products/{id}', 'ProductController@update', [AuthMiddleware::class]);
$router->delete('/api/v1/products/{id}', 'ProductController@destroy', [AuthMiddleware::class]);

$router->dispatch($uri, $method);
