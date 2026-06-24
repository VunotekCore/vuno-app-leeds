<?php

namespace Models;

use Core\Database;
use PDO;

class Lead
{
  public static function findAll(array $filters = [], int $page = 1, int $perPage = 50): array
  {
    $db = Database::getInstance();
    $conditions = [];
    $params = [];

    if (!empty($filters['search'])) {
      $conditions[] = '(store_name LIKE :search OR phone LIKE :search2 OR email LIKE :search3)';
      $params[':search'] = "%{$filters['search']}%";
      $params[':search2'] = "%{$filters['search']}%";
      $params[':search3'] = "%{$filters['search']}%";
    }

    if (!empty($filters['contact_status'])) {
      $conditions[] = 'contact_status = :contact_status';
      $params[':contact_status'] = $filters['contact_status'];
    }

    $where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $db->prepare("SELECT COUNT(*) FROM leads $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $sql = "SELECT BIN_TO_UUID(id) AS id, store_name, profile_url, phone, email,
                   followers_count, tier_classification, contact_status,
                   contact_attempts, last_contact_date,
                   BIN_TO_UUID(created_by) AS created_by, registration_date
            FROM leads $where
            ORDER BY registration_date DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
      'data'  => $stmt->fetchAll(),
      'total' => $total,
    ];
  }

  public static function findById(string $uuid): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT BIN_TO_UUID(id) AS id, store_name, profile_url, phone, email,
              followers_count, tier_classification, contact_status,
              contact_attempts, last_contact_date,
              BIN_TO_UUID(created_by) AS created_by, registration_date
       FROM leads WHERE id = UUID_TO_BIN(:id) LIMIT 1"
    );
    $stmt->execute([':id' => $uuid]);
    $lead = $stmt->fetch();

    return $lead ?: null;
  }

  public static function create(array $data): string
  {
    $db = Database::getInstance();
    $uuid = uuid_v7();

    $stmt = $db->prepare(
      "INSERT INTO leads (id, store_name, profile_url, phone, email,
                          followers_count, tier_classification, contact_status,
                          created_by)
       VALUES (UUID_TO_BIN(:id), :store_name, :profile_url, :phone, :email,
               :followers_count, :tier_classification, :contact_status,
               UUID_TO_BIN(:created_by))"
    );

    $stmt->execute([
      ':id'                  => $uuid,
      ':store_name'          => $data['store_name'],
      ':profile_url'         => $data['profile_url'],
      ':phone'               => sanitize_phone($data['phone']),
      ':email'               => $data['email'] ?? null,
      ':followers_count'     => (int)($data['followers_count'] ?? 0),
      ':tier_classification' => $data['tier_classification'] ?? null,
      ':contact_status'      => $data['contact_status'] ?? 'Pending',
      ':created_by'          => $data['created_by'],
    ]);

    return $uuid;
  }

  public static function update(string $uuid, array $data): bool
  {
    $db = Database::getInstance();
    $fields = [];
    $params = [':id' => $uuid];

    $allowed = ['store_name', 'profile_url', 'email', 'followers_count',
                'tier_classification', 'contact_status'];

    foreach ($allowed as $field) {
      if (array_key_exists($field, $data)) {
        $fields[] = "$field = :$field";

        if ($field === 'phone') {
          $params[":$field"] = sanitize_phone($data[$field]);
        } else {
          $params[":$field"] = $data[$field];
        }
      }
    }

    if (empty($fields)) {
      return false;
    }

    $sql = "UPDATE leads SET " . implode(', ', $fields) . " WHERE id = UUID_TO_BIN(:id)";
    $stmt = $db->prepare($sql);

    return $stmt->execute($params);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM leads WHERE id = UUID_TO_BIN(:id)");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }

  public static function checkDuplicate(string $phone, string $profileUrl): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT BIN_TO_UUID(id) AS id, store_name, phone, profile_url
       FROM leads
       WHERE phone = :phone AND profile_url = :profile_url
       LIMIT 1"
    );
    $stmt->execute([
      ':phone'       => $phone,
      ':profile_url' => $profileUrl,
    ]);
    $lead = $stmt->fetch();

    return $lead ?: null;
  }

  public static function advanceContact(string $uuid): bool
  {
    $db = Database::getInstance();

    $statusFlow = [
      'Pending'        => 'First Contact',
      'First Contact'  => 'Second Contact',
    ];

    $lead = self::findById($uuid);
    if (!$lead) {
      return false;
    }

    $nextStatus = $statusFlow[$lead['contact_status']] ?? $lead['contact_status'];

    $stmt = $db->prepare(
      "UPDATE leads
       SET contact_status = :status,
           contact_attempts = contact_attempts + 1,
           last_contact_date = CURRENT_TIMESTAMP
       WHERE id = UUID_TO_BIN(:id)"
    );

    return $stmt->execute([
      ':id'     => $uuid,
      ':status' => $nextStatus,
    ]);
  }

  public static function getFollowUpAlerts(): array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT BIN_TO_UUID(id) AS id, store_name, profile_url, phone,
              contact_status, contact_attempts, last_contact_date,
              BIN_TO_UUID(created_by) AS created_by
       FROM leads
       WHERE contact_status = 'First Contact'
         AND last_contact_date <= DATE_SUB(NOW(), INTERVAL 2 DAY)
       ORDER BY last_contact_date ASC"
    );
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public static function getDashboardMetrics(): array
  {
    $db = Database::getInstance();

    $stmt = $db->query(
      "SELECT
         COUNT(*) AS total_leads,
         COALESCE(SUM(CASE WHEN contact_status = 'Pending' THEN 1 ELSE 0 END), 0) AS pending,
         COALESCE(SUM(CASE WHEN contact_status = 'First Contact' THEN 1 ELSE 0 END), 0) AS first_contact,
         COALESCE(SUM(CASE WHEN contact_status = 'Second Contact' THEN 1 ELSE 0 END), 0) AS second_contact,
         COALESCE(SUM(CASE WHEN contact_status = 'Interested' THEN 1 ELSE 0 END), 0) AS interested,
         COALESCE(SUM(CASE WHEN contact_status = 'Client' THEN 1 ELSE 0 END), 0) AS clients,
         COALESCE(SUM(CASE WHEN contact_status = 'Archived' THEN 1 ELSE 0 END), 0) AS archived
       FROM leads"
    );

    return $stmt->fetch();
  }
}
