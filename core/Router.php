<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[strtoupper($method)][] = [
            'regex' => $this->compile($pattern),
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $path = $this->currentPath();

        foreach ($this->routes[$method] ?? [] as $route) {
            $matches = [];
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = $value;
                }
            }

            $route['handler'](...$params);
            return;
        }

        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Route introuvable'], JSON_UNESCAPED_UNICODE);
    }

    private function compile(string $pattern): string
    {
        $pattern = '/' . trim($pattern, '/');
        if ($pattern === '/') {
            return '#^/?$#';
        }

        $parts = [];
        foreach (explode('/', trim($pattern, '/')) as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $match) === 1) {
                $parts[] = '(?P<' . $match[1] . '>[^/]+)';
                continue;
            }
            $parts[] = preg_quote($segment, '#');
        }

        return '#^/' . implode('/', $parts) . '/?$#';
    }

    private function currentPath(): string
    {
        $path = $_SERVER['PATH_INFO'] ?? null;

        if (!is_string($path) || $path === '') {
            $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
            $uriPath = is_string($uriPath) && $uriPath !== '' ? $uriPath : '/';
            $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

            if ($script !== '' && str_starts_with($uriPath, $script)) {
                $path = substr($uriPath, strlen($script));
            } else {
                $scriptDir = $script === '' ? '' : dirname($script);
                if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($uriPath, $scriptDir)) {
                    $path = substr($uriPath, strlen($scriptDir));
                } else {
                    $path = $uriPath;
                }
            }
        }

        return '/' . trim(str_replace('\\', '/', $path), '/');
    }
}
