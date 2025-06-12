<?php

namespace App\Core;

use ReflectionMethod;

/**
 * The Router class resolves a request to a specific controller action.
 * This version uses a more robust strategy by separating static and dynamic routes.
 */
class Router
{
    protected array $staticRoutes = [];
    protected array $dynamicRoutes = [];
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
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        if (str_contains($path, '{')) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>\d+)', $path);
            $pattern = '#^' . $pattern . '$#';
            $this->dynamicRoutes[$method][] = [
                'pattern' => $pattern,
                'handler' => $handler,
            ];
        } else {
            $this->staticRoutes[$method][$path] = $handler;
        }
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $handler = $this->staticRoutes[$method][$path] ?? null;
        $routeParams = [];

        if ($handler === null && isset($this->dynamicRoutes[$method])) {
            foreach ($this->dynamicRoutes[$method] as $route) {
                if (preg_match($route['pattern'], $path, $matches)) {
                    $handler = $route['handler'];
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $routeParams[$key] = $value;
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

        [$controllerClass, $methodName] = $handler;

        $controller = new $controllerClass();
        $reflectionMethod = new ReflectionMethod($controller, $methodName);

        $args = [];
        foreach ($reflectionMethod->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if ($paramType && $paramType->getName() === Request::class) {
                $args[] = $this->request;
            } elseif (isset($routeParams[$paramName])) {
                $args[] = $routeParams[$paramName];
            }
        }

        call_user_func_array([$controller, $methodName], $args);
    }
}
