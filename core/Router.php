<?php

namespace Core;

class Router
{
    protected array $routes = [];
    protected array $groupStack = [];
    protected array $middlewareAliases = [];

    public function registerMiddleware(array $aliases): void
    {
        $this->middlewareAliases = array_merge($this->middlewareAliases, $aliases);
    }

    public function get(string $uri, mixed $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, mixed $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, mixed $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, mixed $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    public function group(array $attributes, \Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    protected function addRoute(string $method, string $uri, mixed $action): void
    {
        $prefix = '';
        $middleware = [];

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'] ?? '';
            $middleware = array_merge($middleware, $group['middleware'] ?? []);
        }

        $uri = '/' . trim($prefix . $uri, '/');

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware,
            'pattern' => $this->toPattern($uri),
        ];
    }

    protected function toPattern(string $uri): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, fn ($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
                $request->params = $params;

                if (!$this->runMiddleware($route['middleware'], $request)) {
                    return;
                }

                $this->callAction($route['action'], $request, $params);
                return;
            }
        }

        ErrorHandler::render(404);
    }

    protected function runMiddleware(array $middlewareList, Request $request): bool
    {
        foreach ($middlewareList as $middleware) {
            [$name, $arg] = array_pad(explode(':', $middleware, 2), 2, null);

            $class = $this->middlewareAliases[$name] ?? null;

            if ($class === null || !class_exists($class)) {
                continue;
            }

            $instance = new $class();
            $result = $arg !== null ? $instance->handle($request, $arg) : $instance->handle($request);

            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    protected function callAction(mixed $action, Request $request, array $params): void
    {
        if ($action instanceof \Closure) {
            call_user_func($action, $request, ...array_values($params));
            return;
        }

        if (is_array($action)) {
            [$controllerClass, $methodName] = $action;
            $controller = new $controllerClass();
            call_user_func([$controller, $methodName], $request, ...array_values($params));
            return;
        }
    }
}
