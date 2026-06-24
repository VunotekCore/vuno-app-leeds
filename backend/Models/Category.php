<?php

namespace Models;

use Core\Database;
use PDO;

class Category
{
  public static function findAll(): array
  {
    $db = Database::getInstance();
    $stmt = $db->query(
      "SELECT id, name, created_by, created_at, updated_at
       FROM categories ORDER BY name ASC"
    );

    return $stmt->fetchAll();
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT id, name, created_by, created_at, updated_at
       FROM categories WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $uuid]);

    $category = $stmt->fetch();
    return $category ?: null;
  }

  public static function create(array $data): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO categories (id, name, created_by)
       VALUES (:id, :name, :created_by)"
    );

    $stmt->execute([
      ':id'         => $uuid,
      ':name'       => $data['name'],
      ':created_by' => $data['created_by'],
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "UPDATE categories
       SET name = :name
       WHERE id = :id"
    );

    return $stmt->execute([
      ':id'   => $uuid,
      ':name' => $data['name'],
    ]);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }
}
