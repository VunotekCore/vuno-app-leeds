<?php

namespace Models;

use Core\Database;
use PDO;

class User
{
  public static function findAll(): array
  {
    $db = Database::getInstance();
    $stmt = $db->query(
      "SELECT id, email, whatsapp_apikey, created_at
       FROM users ORDER BY created_at DESC"
    );
    return $stmt->fetchAll();
  }

  public static function findByEmail(string $email): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT id, email, password, whatsapp_apikey, created_at
       FROM users WHERE email = :email LIMIT 1"
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    return $user ?: null;
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT id, email, whatsapp_apikey, created_at
       FROM users WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $uuid]);
    $user = $stmt->fetch();

    return $user ?: null;
  }

  public static function validatePassword(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }

  public static function create(array $data): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO users (id, email, password)
       VALUES (:id, :email, :password)"
    );

    $stmt->execute([
      ':id'       => $uuid,
      ':email'    => $data['email'],
      ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $fields = [];
    $params = [':id' => $uuid];

    if (isset($data['email'])) {
      $fields[] = 'email = :email';
      $params[':email'] = $data['email'];
    }

    if (!empty($data['password'])) {
      $fields[] = 'password = :password';
      $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    if (empty($fields)) return false;

    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $uuid]);
    return $stmt->rowCount() > 0;
  }

  public static function updateApikey(string $uuid, ?string $apikey): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "UPDATE users SET whatsapp_apikey = :apikey WHERE id = :id"
    );
    return $stmt->execute([
      ':id'     => $uuid,
      ':apikey' => $apikey,
    ]);
  }
}
