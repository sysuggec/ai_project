<?php
declare(strict_types=1);

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Application
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function use(object $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function run(): void
    {
        $request = Request::createFromGlobals();

        foreach ($this->middlewares as $middleware) {
            if (method_exists($middleware, 'handle')) {
                $response = $middleware->handle($request);
                if ($response !== null) {
                    $response->send();
                    return;
                }
            }
        }

        $response = $this->handleRequest($request);
        $response->send();
    }

    private function handleRequest(Request $request): SymfonyResponse
    {
        $method = $request->getMethod();
        $path = $request->getPathInfo();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToPattern($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return call_user_func_array($route['handler'], [$request, ...$matches]);
            }
        }

        return Response::json(['error' => 'Not Found'], 404);
    }

    private function convertPathToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
