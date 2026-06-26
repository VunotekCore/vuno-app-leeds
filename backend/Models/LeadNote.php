<?php

namespace Models;

use Core\Database;
use PDO;

class LeadNote
{
  public static function findByLead(string $leadId): array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT n.id, n.note, n.created_at, u.email AS created_by_email
       FROM lead_notes n
       LEFT JOIN users u ON u.id = n.created_by
       WHERE n.lead_id = :lead_id
       ORDER BY n.created_at DESC"
    );
    $stmt->execute([':lead_id' => $leadId]);

    return $stmt->fetchAll();
  }

  public static function create(string $leadId, string $note, string $createdBy): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO lead_notes (id, lead_id, note, created_by)
       VALUES (:id, :lead_id, :note, :created_by)"
    );

    $stmt->execute([
      ':id'         => $uuid,
      ':lead_id'    => $leadId,
      ':note'       => $note,
      ':created_by' => $createdBy,
    ]);

    return $uuid;
  }

  public static function logStatusChange(
    string $leadId,
    string $oldStatus,
    string $newStatus,
    string $userId,
    string $userEmail,
    ?string $action = null,
    ?string $detail = null,
  ): string {
    $note = match ($action) {
      'send' => "{$userEmail} sent {$detail}, status changed from {$oldStatus} to {$newStatus}",
      default => "{$userEmail} changed status from {$oldStatus} to {$newStatus}",
    };
    return self::create($leadId, $note, $userId);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM lead_notes WHERE id = :id");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }
}
