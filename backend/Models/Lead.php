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
      $conditions[] = '(l.store_name LIKE :search OR l.phone LIKE :search2 OR l.email LIKE :search3)';
      $params[':search'] = "%{$filters['search']}%";
      $params[':search2'] = "%{$filters['search']}%";
      $params[':search3'] = "%{$filters['search']}%";
    }

    if (!empty($filters['category_id'])) {
      $conditions[] = 'l.category_id = :category_id';
      $params[':category_id'] = $filters['category_id'];
    }

    if (!empty($filters['contact_status'])) {
      $statuses = array_map('trim', explode(',', $filters['contact_status']));
      if (count($statuses) === 1) {
        $conditions[] = 'l.contact_status = :contact_status';
        $params[':contact_status'] = $statuses[0];
      } else {
        $placeholders = [];
        foreach ($statuses as $i => $s) {
          $key = ":contact_status_$i";
          $placeholders[] = $key;
          $params[$key] = $s;
        }
        $conditions[] = 'l.contact_status IN (' . implode(',', $placeholders) . ')';
      }
    }

    $where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $countStmt = $db->prepare("SELECT COUNT(*) FROM leads l $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $sql = "SELECT l.id, l.store_name, l.profile_url, l.phone, l.email,
                   l.followers_count, l.tier_classification, l.contact_status,
                   l.contact_attempts, l.last_contact_date, l.product_id,
                   l.category_id, l.created_by, l.registration_date,
                   p.name AS product_name,
                   c.name AS category_name
            FROM leads l
            LEFT JOIN products p ON p.id = l.product_id
            LEFT JOIN categories c ON c.id = l.category_id
            $where
            ORDER BY l.registration_date DESC
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
      "SELECT l.id, l.store_name, l.profile_url, l.phone, l.email,
              l.followers_count, l.tier_classification, l.contact_status,
              l.contact_attempts, l.last_contact_date, l.product_id,
              l.category_id, l.created_by, l.registration_date,
              p.name AS product_name,
              c.name AS category_name
       FROM leads l
       LEFT JOIN products p ON p.id = l.product_id
       LEFT JOIN categories c ON c.id = l.category_id
       WHERE l.id = :id LIMIT 1"
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
                          followers_count, tier_classification, product_id,
                          category_id, contact_status,
                          created_by)
       VALUES (:id, :store_name, :profile_url, :phone, :email,
               :followers_count, :tier_classification, :product_id,
               :category_id, :contact_status,
               :created_by)"
    );

    $stmt->execute([
      ':id'                  => $uuid,
      ':store_name'          => $data['store_name'],
      ':profile_url'         => $data['profile_url'],
      ':phone'               => sanitize_phone($data['phone']),
      ':email'               => $data['email'] ?? null,
      ':followers_count'     => (int)($data['followers_count'] ?? 0),
      ':tier_classification' => $data['tier_classification'] ?? null,
      ':product_id'          => $data['product_id'] ?? null,
      ':category_id'         => $data['category_id'] ?? null,
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
                'tier_classification', 'product_id', 'category_id', 'contact_status'];

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

    $sql = "UPDATE leads SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);

    return $stmt->execute($params);
  }

  public static function delete(string $uuid): bool
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM leads WHERE id = :id");
    $stmt->execute([':id' => $uuid]);

    return $stmt->rowCount() > 0;
  }

  public static function checkDuplicate(string $phone, string $profileUrl): ?array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT id, store_name, phone, profile_url
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
       WHERE id = :id"
    );

    return $stmt->execute([
      ':id'     => $uuid,
      ':status' => $nextStatus,
    ]);
  }

  public static function getPageData(): array
  {
    $db = Database::getInstance();

    $countStmt = $db->query(
      "SELECT
          COALESCE(SUM(CASE WHEN contact_status IN ('Pending','First Contact','Second Contact','Interested') THEN 1 ELSE 0 END), 0) AS prospecting,
          COALESCE(SUM(CASE WHEN contact_status = 'Client' THEN 1 ELSE 0 END), 0) AS clients,
          COALESCE(SUM(CASE WHEN contact_status = 'Archived' THEN 1 ELSE 0 END), 0) AS archived
       FROM leads"
    );
    $counts = $countStmt->fetch();

    $stmt = $db->prepare(
      "SELECT l.id, l.store_name, l.profile_url, l.phone, l.email,
              l.followers_count, l.tier_classification, l.contact_status,
              l.contact_attempts, l.last_contact_date, l.product_id,
              l.category_id, l.created_by, l.registration_date,
              p.name AS product_name,
              c.name AS category_name
       FROM leads l
       LEFT JOIN products p ON p.id = l.product_id
       LEFT JOIN categories c ON c.id = l.category_id
       ORDER BY l.registration_date DESC
       LIMIT 500"
    );
    $stmt->execute();
    $allLeads = $stmt->fetchAll();

    $prospectingStatuses = ['Pending', 'First Contact', 'Second Contact', 'Interested'];
    $leads = ['prospecting' => [], 'clients' => [], 'archived' => []];

    foreach ($allLeads as $lead) {
      if (in_array($lead['contact_status'], $prospectingStatuses)) {
        $leads['prospecting'][] = $lead;
      } elseif ($lead['contact_status'] === 'Client') {
        $leads['clients'][] = $lead;
      } elseif ($lead['contact_status'] === 'Archived') {
        $leads['archived'][] = $lead;
      }
    }

    return [
      'leads'  => $leads,
      'counts' => $counts,
    ];
  }

  public static function getFollowUpAlerts(): array
  {
    $db = Database::getInstance();
    $stmt = $db->prepare(
      "SELECT l.id, l.store_name, l.profile_url, l.phone,
              l.contact_status, l.contact_attempts, l.last_contact_date,
              l.product_id, l.category_id, l.created_by,
              p.name AS product_name,
              c.name AS category_name
       FROM leads l
       LEFT JOIN products p ON p.id = l.product_id
       LEFT JOIN categories c ON c.id = l.category_id
       WHERE l.contact_status = 'First Contact'
         AND l.last_contact_date <= DATE_SUB(NOW(), INTERVAL 2 DAY)
       ORDER BY l.last_contact_date ASC"
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
