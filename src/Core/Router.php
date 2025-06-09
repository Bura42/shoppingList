<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    protected array $routes = [];
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(string $path, array $handler): void
    {
        $this->addRoute('get', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('post', $path, $handler);
    }

    protected function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        $handler = $this->routes[$method][$path] ?? null;
        $params = [];

        if ($handler === null) {
            foreach ($this->routes[$method] as $routePath => $routeHandler) {
                $routePath = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[0-9]+)', $routePath);
                $routePath = '#^' . $routePath . '$#';

                if (preg_match($routePath, $path, $matches)) {
                    $handler = $routeHandler;
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = (int)$value;
                        }
                    }
                    break;
                }
            }
        }

        if ($handler === null) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        [$controllerClass, $method] = $handler;

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $method)) {
            echo "500 - Server Error";
            return;
        }

        $controller = new $controllerClass();
        call_user_func_array([$controller, $method], [$this->request, ...array_values($params)]);
    }
}
