<?php

namespace Core;

class Router
{
  private array $routes = [];

  public function add(string $method, string $path, callable|array $handler, bool $protected = true): void
  {
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
    $pattern = '#^' . $pattern . '$#';

    $this->routes[] = [
      'method'    => strtoupper($method),
      'pattern'   => $pattern,
      'handler'   => $handler,
      'protected' => $protected,
    ];
  }

  public function get(string $path, callable|array $handler, bool $protected = true): void
  {
    $this->add('GET', $path, $handler, $protected);
  }

  public function post(string $path, callable|array $handler, bool $protected = true): void
  {
    $this->add('POST', $path, $handler, $protected);
  }

  public function put(string $path, callable|array $handler, bool $protected = true): void
  {
    $this->add('PUT', $path, $handler, $protected);
  }

  public function delete(string $path, callable|array $handler, bool $protected = true): void
  {
    $this->add('DELETE', $path, $handler, $protected);
  }

  public function dispatch(): void
  {
    $method = Request::method();
    $path = Request::path();

    $app = require __DIR__ . '/../config/app.php';

    header('Access-Control-Allow-Origin: ' . $app['cors_origin']);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($method === 'OPTIONS') {
      http_response_code(204);
      exit;
    }

    foreach ($this->routes as $route) {
      if ($route['method'] !== $method) {
        continue;
      }

      if (preg_match($route['pattern'], $path, $matches)) {
        if ($route['protected']) {
          JwtMiddleware::authenticate();
        }

        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        $handler = $route['handler'];

        if (is_array($handler)) {
          [$class, $action] = $handler;
          $controller = new $class();
          $controller->$action($params);
        } else {
          $handler($params);
        }

        return;
      }
    }

    Response::error('Not Found', 404);
  }
}
