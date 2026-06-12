<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    private ?Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db;
    }

    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    public function put(string $path, string $controller, string $action): void
    {
        $this->add('PUT', $path, $controller, $action);
    }

    public function delete(string $path, string $controller, string $action): void
    {
        $this->add('DELETE', $path, $controller, $action);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];
            if (!$this->matchPath($route['path'], $path, $params)) {
                continue;
            }

            $controller = new $route['controller']($this->db);
            return $controller->{$route['action']}($request, $params);
        }

        $response = new Response();
        $response->setStatus(404);
        $response->json(['error' => 'Not Found']);
        return $response;
    }

    private function matchPath(string $routePath, string $requestPath, array &$params): bool
    {
        preg_match_all('/\{([a-zA-Z_]+)\}/', $routePath, $nameMatches);
        $paramNames = $nameMatches[1];

        $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return false;
        }

        foreach ($paramNames as $index => $name) {
            $params[$name] = $matches[$index + 1];
        }

        return true;
    }
}
