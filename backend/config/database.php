<?php

return [
  'host'     => getenv('DB_HOST') ?: 'localhost',
  'port'     => getenv('DB_PORT') ?: '3306',
  'dbname'   => getenv('DB_NAME') ?: 'vuno_app_leed',
  'username' => getenv('DB_USER') ?: 'dail',
  'password' => getenv('DB_PASS') ?: 'DB_PASS_PLACEHOLDER',
  'socket'   => getenv('DB_SOCKET') ?: '',
  'charset'  => 'utf8mb4',
];
