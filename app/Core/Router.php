<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path, callable|array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function any(string $path, callable|array $handler): void
    {
        $this->map('GET', $path, $handler);
        $this->map('POST', $path, $handler);
    }

    private function map(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$this->normalize($path)] = $handler;
    }

    private function normalize(string $path): string
    {
        return '/' . trim($path, '/');
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim(str_replace(parse_url($this->baseUrl, PHP_URL_PATH), '', $path), '/');
        if ($path === '//') { $path = '/'; }

        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo View::render('errors/404', ['path' => $path]);
            return;
        }

        if (is_array($handler)) {
            [$class, $action] = $handler;
            $controller = new $class();
            call_user_func([$controller, $action]);
            return;
        }

        call_user_func($handler);
    }
}
