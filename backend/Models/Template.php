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
      "SELECT BIN_TO_UUID(id) AS id, template_name, message_body,
              BIN_TO_UUID(created_by) AS created_by, created_at, updated_at
       FROM templates ORDER BY created_at DESC"
    );

    return $stmt->fetchAll();
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT BIN_TO_UUID(id) AS id, template_name, message_body,
              BIN_TO_UUID(created_by) AS created_by, created_at, updated_at
       FROM templates WHERE id = UUID_TO_BIN(:id) LIMIT 1"
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
      "INSERT INTO templates (id, template_name, message_body, created_by)
       VALUES (UUID_TO_BIN(:id), :template_name, :message_body, UUID_TO_BIN(:created_by))"
    );

    $stmt->execute([
      ':id'            => $uuid,
      ':template_name' => $data['template_name'],
      ':message_body'  => $data['message_body'],
      ':created_by'    => $data['created_by'],
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "UPDATE templates
       SET template_name = :template_name, message_body = :message_body
       WHERE id = UUID_TO_BIN(:id)"
    );

    return $stmt->execute([
      ':id'            => $uuid,
      ':template_name' => $data['template_name'],
      ':message_body'  => $data['message_body'],
    ]);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM templates WHERE id = UUID_TO_BIN(:id)");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }
}
