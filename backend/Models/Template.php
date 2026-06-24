<?php

namespace Models;

use Core\Database;
use PDO;

class Template
{
  public static function findAll(): array
  {
    $db = Database::getInstance();
    $stmt = $db->query(
      "SELECT t.id, t.template_name, t.message_body, t.product_id,
              t.created_by, t.created_at, t.updated_at,
              p.name AS product_name
       FROM templates t
       LEFT JOIN products p ON p.id = t.product_id
       ORDER BY t.created_at DESC"
    );

    return $stmt->fetchAll();
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT t.id, t.template_name, t.message_body, t.product_id,
              t.created_by, t.created_at, t.updated_at,
              p.name AS product_name
       FROM templates t
       LEFT JOIN products p ON p.id = t.product_id
       WHERE t.id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $uuid]);

    $template = $stmt->fetch();
    return $template ?: null;
  }

  public static function create(array $data): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO templates (id, template_name, message_body, product_id, created_by)
       VALUES (:id, :template_name, :message_body, :product_id, :created_by)"
    );

    $stmt->execute([
      ':id'            => $uuid,
      ':template_name' => $data['template_name'],
      ':message_body'  => $data['message_body'],
      ':product_id'    => $data['product_id'] ?? null,
      ':created_by'    => $data['created_by'],
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "UPDATE templates
       SET template_name = :template_name, message_body = :message_body, product_id = :product_id
       WHERE id = :id"
    );

    return $stmt->execute([
      ':id'            => $uuid,
      ':template_name' => $data['template_name'],
      ':message_body'  => $data['message_body'],
      ':product_id'    => $data['product_id'] ?? null,
    ]);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM templates WHERE id = :id");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }
}
