<?php

$jwtSecret = getenv('JWT_SECRET');
if (!$jwtSecret) {
  die('JWT_SECRET environment variable is required');
}

return [
  'jwt_secret'   => $jwtSecret,
  'jwt_expiry'   => 86400,
  'cors_origin'  => '*',
  'country_code' => '505',
];
