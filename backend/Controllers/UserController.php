<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\User;

class UserController
{
  public function index(): void
  {
    $users = User::findAll();
    Response::success($users);
  }

  public function show(array $params): void
  {
    $user = User::findById($params['id']);
    if (!$user) {
      Response::error('User not found', 404);
    }
    Response::success($user);
  }

  public function store(): void
  {
    $data = Request::body();

    if (empty($data['email']) || empty($data['password'])) {
      Response::error('email and password are required', 422);
    }

    $existing = User::findByEmail($data['email']);
    if ($existing) {
      Response::error('Email already exists', 409);
    }

    $uuid = User::create($data);
    $user = User::findById($uuid);

    Response::success($user, 'User created successfully', 201);
  }

  public function update(array $params): void
  {
    $user = User::findById($params['id']);
    if (!$user) {
      Response::error('User not found', 404);
    }

    $data = Request::body();
    User::update($params['id'], $data);
    $updated = User::findById($params['id']);

    Response::success($updated, 'User updated successfully');
  }

  public function destroy(array $params): void
  {
    $user = User::findById($params['id']);
    if (!$user) {
      Response::error('User not found', 404);
    }

    if ($user['email'] === 'dail') {
      Response::error('Cannot delete main user', 403);
    }

    User::delete($params['id']);
    Response::success(null, 'User deleted successfully');
  }

  public function updateApikey(array $params): void
  {
    $user = User::findById($params['id']);
    if (!$user) {
      Response::error('User not found', 404);
    }

    $data = Request::body();
    $apikey = $data['apikey'] ?? null;
    User::updateApikey($params['id'], $apikey);

    $updated = User::findById($params['id']);
    Response::success([
      'whatsapp_apikey' => $updated['whatsapp_apikey'],
    ], 'API key updated successfully');
  }
}
