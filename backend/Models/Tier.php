<?php

namespace Models;

use Core\Database;
use PDO;

class Tier
{
  public static function findAll(): array
  {
    $db = Database::getInstance();
    $stmt = $db->query(
      "SELECT id, name, price,
              created_by, created_at, updated_at
       FROM tiers ORDER BY price ASC"
    );

    return $stmt->fetchAll();
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT id, name, price,
              created_by, created_at, updated_at
       FROM tiers WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $uuid]);

    $tier = $stmt->fetch();
    return $tier ?: null;
  }

  public static function create(array $data): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO tiers (id, name, price, created_by)
       VALUES (:id, :name, :price, :created_by)"
    );

    $stmt->execute([
      ':id'         => $uuid,
      ':name'       => $data['name'],
      ':price'      => $data['price'],
      ':created_by' => $data['created_by'],
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "UPDATE tiers
       SET name = :name, price = :price
       WHERE id = :id"
    );

    return $stmt->execute([
      ':id'    => $uuid,
      ':name'  => $data['name'],
      ':price' => $data['price'],
    ]);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM tiers WHERE id = :id");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }
}
