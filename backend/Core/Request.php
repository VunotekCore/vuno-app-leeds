<?php

namespace Core;

class Request
{
  public static function method(): string
  {
    return $_SERVER['REQUEST_METHOD'];
  }

  public static function path(): string
  {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = parse_url($uri, PHP_URL_PATH);
    return rtrim($uri, '/') ?: '/';
  }

  public static function body(): array
  {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
      $raw = file_get_contents('php://input');
      $data = json_decode($raw, true);
      return is_array($data) ? $data : [];
    }

    return $_POST;
  }

  public static function query(string $key, mixed $default = null): mixed
  {
    return $_GET[$key] ?? $default;
  }

  public static function all(): array
  {
    return array_merge($_GET, self::body());
  }
}
