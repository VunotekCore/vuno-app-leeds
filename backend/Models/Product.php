<?php

namespace Models;

use Core\Database;
use PDO;

class Product
{
  public static function findAll(): array
  {
    $db = Database::getInstance();
    $stmt = $db->query(
      "SELECT id, name, description, base_price,
              created_by, created_at, updated_at
       FROM products ORDER BY name ASC"
    );

    return $stmt->fetchAll();
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT id, name, description, base_price,
              created_by, created_at, updated_at
       FROM products WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $uuid]);

    $product = $stmt->fetch();
    return $product ?: null;
  }

  public static function create(array $data): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO products (id, name, description, base_price, created_by)
       VALUES (:id, :name, :description, :base_price, :created_by)"
    );

    $stmt->execute([
      ':id'          => $uuid,
      ':name'        => $data['name'],
      ':description' => $data['description'] ?? null,
      ':base_price'  => $data['base_price'] ?? null,
      ':created_by'  => $data['created_by'],
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "UPDATE products
       SET name = :name, description = :description, base_price = :base_price
       WHERE id = :id"
    );

    return $stmt->execute([
      ':id'          => $uuid,
      ':name'        => $data['name'],
      ':description' => $data['description'] ?? null,
      ':base_price'  => $data['base_price'] ?? null,
    ]);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }
}