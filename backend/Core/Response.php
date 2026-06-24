<?php

namespace Core;

class Response
{
  public static function json(mixed $data, int $status = 200, array $headers = []): void
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    foreach ($headers as $key => $value) {
      header("$key: $value");
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  public static function success(mixed $data = null, string $message = 'OK', int $status = 200): void
  {
    self::json([
      'success' => true,
      'message' => $message,
      'data'    => $data,
    ], $status);
  }

  public static function error(string $message, int $status = 400, mixed $errors = null): void
  {
    $payload = [
      'success' => false,
      'message' => $message,
    ];

    if ($errors !== null) {
      $payload['errors'] = $errors;
    }

    self::json($payload, $status);
  }

  public static function paginated(array $data, int $total, int $page, int $perPage): void
  {
    self::json([
      'success' => true,
      'data'    => $data,
      'meta'    => [
        'total'        => $total,
        'page'         => $page,
        'per_page'     => $perPage,
        'total_pages'  => (int)ceil($total / $perPage),
      ],
    ]);
  }
}
