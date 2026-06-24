<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
  private static ?PDO $instance = null;

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      $config = require __DIR__ . '/../config/database.php';

      if (!empty($config['socket'])) {
        $dsn = sprintf(
          'mysql:unix_socket=%s;dbname=%s;charset=%s',
          $config['socket'],
          $config['dbname'],
          $config['charset']
        );
      } else {
        $dsn = sprintf(
          'mysql:host=%s;port=%s;dbname=%s;charset=%s',
          $config['host'],
          $config['port'],
          $config['dbname'],
          $config['charset']
        );
      }

      try {
        self::$instance = new PDO($dsn, $config['username'], $config['password'], [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
      } catch (PDOException $e) {
        Response::error('Database connection failed: ' . $e->getMessage(), 500);
        exit;
      }
    }

    return self::$instance;
  }
}
