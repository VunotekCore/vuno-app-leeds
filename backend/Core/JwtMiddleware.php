<?php

namespace Core;

class JwtMiddleware
{
  public static function authenticate(): void
  {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (empty($header)) {
      Response::error('Authorization header required', 401);
    }

    $parts = explode(' ', $header);
    if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
      Response::error('Invalid authorization format. Use: Bearer <token>', 401);
    }

    $token = $parts[1];
    $payload = self::verify($token);

    if ($payload === null) {
      Response::error('Invalid or expired token', 401);
    }

    $_REQUEST['auth_user'] = $payload;
  }

  public static function createToken(array $payload): string
  {
    $app = require __DIR__ . '/../config/app.php';
    $secret = $app['jwt_secret'];

    $header = self::base64UrlEncode(json_encode([
      'alg' => 'HS256',
      'typ' => 'JWT',
    ]));

    $payload['iat'] = time();
    $payload['exp'] = time() + $app['jwt_expiry'];

    $payloadEncoded = self::base64UrlEncode(json_encode($payload));

    $signature = self::base64UrlEncode(
      hash_hmac('sha256', "$header.$payloadEncoded", $secret, true)
    );

    return "$header.$payloadEncoded.$signature";
  }

  public static function verify(string $token): ?array
  {
    $app = require __DIR__ . '/../config/app.php';
    $secret = $app['jwt_secret'];

    $parts = explode('.', $token);
    if (count($parts) !== 3) {
      return null;
    }

    [$header, $payload, $signature] = $parts;

    $expectedSignature = self::base64UrlEncode(
      hash_hmac('sha256', "$header.$payload", $secret, true)
    );

    if (!hash_equals($expectedSignature, $signature)) {
      return null;
    }

    $data = json_decode(self::base64UrlDecode($payload), true);

    if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
      return null;
    }

    return $data;
  }

  private static function base64UrlEncode(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  private static function base64UrlDecode(string $data): string
  {
    return base64_decode(strtr($data, '-_', '+/'));
  }
}
