<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, array|Closure $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array|Closure $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array|Closure $handler): void
    {
        $params = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $match) use (&$params): string {
            $params[] = $match[1];
            return '([^/]+)';
        }, $pattern);

        $this->routes[] = [
            'method' => $method,
            'regex' => '#^' . rtrim($regex ?: '/', '/') . '/?$#u',
            'params' => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            if (!preg_match($route['regex'], rtrim($request->path(), '/') ?: '/', $matches)) {
                continue;
            }

            array_shift($matches);
            $arguments = [$request];
            foreach ($route['params'] as $index => $name) {
                $arguments[] = $matches[$index] ?? null;
            }

            if ($route['handler'] instanceof Closure) {
                $route['handler'](...$arguments);
                return;
            }

            [$class, $method] = $route['handler'];
            if (!class_exists($class) || !method_exists($class, $method)) {
                throw new RuntimeException("Invalid route handler: {$class}::{$method}");
            }
            (new $class())->{$method}(...$arguments);
            return;
        }

        http_response_code(404);
        View::render('pages/404', ['pageTitle' => __('not_found')]);
    }
}
