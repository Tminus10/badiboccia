<?php

final class Router
{
    /** @var array<int, array{method:string, regex:string, params:string[], handler:callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $paramNames = [];
        $regex = preg_replace_callback('#\{(\w+)\}#', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $path);

        $this->routes[] = [
            'method' => $method,
            'regex' => '#^' . $regex . '$#',
            'params' => $paramNames,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(base_path(), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                $params = [];
                foreach ($route['params'] as $name) {
                    $params[$name] = $matches[$name];
                }
                ($route['handler'])($params);
                return;
            }
        }

        http_response_code(404);
        render('404');
    }
}
