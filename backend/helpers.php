<?php

function uuid_v7(): string {
  $milliseconds = (int)(microtime(true) * 1000);

  $timeBytes = '';
  $timeBytes .= chr(($milliseconds >> 40) & 0xFF);
  $timeBytes .= chr(($milliseconds >> 32) & 0xFF);
  $timeBytes .= chr(($milliseconds >> 24) & 0xFF);
  $timeBytes .= chr(($milliseconds >> 16) & 0xFF);
  $timeBytes .= chr(($milliseconds >> 8) & 0xFF);
  $timeBytes .= chr($milliseconds & 0xFF);

  $random = random_bytes(10);

  $bytes = $timeBytes;
  $bytes .= chr((ord($random[0]) & 0x0F) | 0x70);
  $bytes .= chr((ord($random[1])));
  $bytes .= chr((ord($random[2]) & 0x3F) | 0x80);
  $bytes .= substr($random, 3, 7);

  $hex = bin2hex($bytes);
  return sprintf(
    '%s-%s-%s-%s-%s',
    substr($hex, 0, 8),
    substr($hex, 8, 4),
    substr($hex, 12, 4),
    substr($hex, 16, 4),
    substr($hex, 20, 12)
  );
}

function sanitize_phone(string $phone, string $countryCode = '505'): string {
  $cleaned = preg_replace('/[^0-9]/', '', $phone);

  if (strlen($cleaned) === 8) {
    $cleaned = $countryCode . $cleaned;
  }

  return $cleaned;
}

function render_template(string $body, array $replacements): string {
  $search = [];
  $replace = [];

  foreach ($replacements as $key => $value) {
    $search[] = "[$key]";
    $replace[] = $value;
  }

  return str_replace($search, $replace, $body);
}
