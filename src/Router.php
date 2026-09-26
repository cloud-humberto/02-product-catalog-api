<?php
declare(strict_types=1);

namespace Api;

use Api\Utils\Response;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string|callable $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method'      => strtoupper($method),
            'path'        => $path,
            'handler'     => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function get(string $path, string|callable $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, string|callable $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, string|callable $handler, array $middlewares = []): void
    {
        $this->add('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, string|callable $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $handler, $middlewares);
    }

    public function dispatch(string $uri, string $method): void
    {
        // Pre-flight CORS
        if ($method === 'OPTIONS') {
            Response::json(200, ['status' => 'ok']);
        }

        $parsedPath = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert {param} to named regex pattern
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $parsedPath, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middlewares
                $middlewareContext = [];
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middlewareContext[] = $middlewareClass::handle();
                }

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func($handler, $params);
                    return;
                }

                if (is_string($handler) && str_contains($handler, '@')) {
                    [$class, $action] = explode('@', $handler);
                    $fullClass = "Api\\Controllers\\{$class}";

                    if (!class_exists($fullClass)) {
                        Response::error("Controller '{$fullClass}' not found.", 500);
                    }

                    $controller = new $fullClass();

                    if (!method_exists($controller, $action)) {
                        Response::error("Action '{$action}' not found in Controller.", 500);
                    }

                    $controller->$action($params);
                    return;
                }
            }
        }

        Response::error('Route not found or method not allowed.', 404);
    }
}
