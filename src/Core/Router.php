<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Router
{
    /**
     * @var array<int, array{method: string, path: string, action: array{0: class-string, 1: string}}>
     */
    private array $routes = [];

    /**
     * @param array{0: class-string, 1: string} $action
     */
    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    public function put(string $path, array $action): void
    {
        $this->add('PUT', $path, $action);
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    public function delete(string $path, array $action): void
    {
        $this->add('DELETE', $path, $action);
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $matches = $this->match($route['path'], $path);

            if ($matches === null) {
                continue;
            }

            [$controllerClass, $controllerMethod] = $route['action'];
            $controller = new $controllerClass();

            try {
                $controller->{$controllerMethod}(...$matches);
            } catch (Throwable $exception) {
                throw $exception;
            }

            return;
        }

        abort_not_found();
    }

    /**
     * @param array{0: class-string, 1: string} $action
     */
    private function add(string $method, string $path, array $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
        ];
    }

    /**
     * @return list<int>|null
     */
    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '(\d+)', $routePath);

        if ($pattern === null) {
            return null;
        }

        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches) !== 1) {
            return null;
        }

        array_shift($matches);

        return array_map(static fn (string $value): int => (int) $value, $matches);
    }
}
