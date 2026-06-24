<?php

namespace Controllers;

use Core\JwtMiddleware;
use Core\Request;
use Core\Response;
use Models\User;

class AuthController
{
  public function login(): void
  {
    $data = Request::body();

    if (empty($data['email']) || empty($data['password'])) {
      Response::error('Email and password required', 422);
    }

    $user = User::findByEmail($data['email']);

    if (!$user || !User::validatePassword($data['password'], $user['password'])) {
      Response::error('Invalid credentials', 401);
    }

    $token = JwtMiddleware::createToken([
      'sub'   => $user['id'],
      'email' => $user['email'],
    ]);

    Response::success([
      'token' => $token,
      'user'  => [
        'id'    => $user['id'],
        'email' => $user['email'],
      ],
    ], 'Login successful');
  }

  public function me(): void
  {
    $userId = $_REQUEST['auth_user']['sub'];
    $user = User::findById($userId);

    Response::success([
      'id'              => $user['id'],
      'email'           => $user['email'],
      'whatsapp_apikey' => $user['whatsapp_apikey'],
    ]);
  }

}
