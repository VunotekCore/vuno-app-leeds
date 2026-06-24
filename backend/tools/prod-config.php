#!/usr/bin/env php
<?php
/**
 * Genera config/database.php y config/app.php con credenciales planas
 * para producción (Hostinger).
 *
 * Uso: php tools/prod-config.php
 *
 * Las credenciales quedan protegidas por config/.htaccess (Deny from all)
 * y Apache nunca las sirve como texto — igual que WordPress con wp-config.php.
 */

$dbPass   = readline('DB_PASS: ');
$jwtSecret = readline('JWT_SECRET: ');

if (!$dbPass || !$jwtSecret) {
  fwrite(STDERR, "Both values are required\n");
  exit(1);
}

$dbConfig = <<<PHP
<?php

return [
  'host'     => 'localhost',
  'port'     => '3306',
  'dbname'   => 'vuno_app_leed',
  'username' => 'dail',
  'password' => '$dbPass',
  'socket'   => '',
  'charset'  => 'utf8mb4',
];

PHP;

$appConfig = <<<PHP
<?php

return [
  'jwt_secret'   => '$jwtSecret',
  'jwt_expiry'   => 86400,
  'cors_origin'  => '*',
  'country_code' => '505',
];

PHP;

$dbFile  = __DIR__ . '/../config/database.php';
$appFile = __DIR__ . '/../config/app.php';

file_put_contents($dbFile, $dbConfig);
file_put_contents($appFile, $appConfig);

echo "✓ database.php written with plain credentials\n";
echo "✓ app.php written with plain JWT secret\n";
echo "\n";
echo "  Both files are protected by config/.htaccess (Deny from all)\n";
echo "  — same security model as WordPress wp-config.php\n";
