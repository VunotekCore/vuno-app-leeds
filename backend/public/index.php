<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ── Serve frontend dist for non-API routes ──────────────────────
if (!str_starts_with($uri, '/api/')) {
  // Buscar dist en desarrollo o producción
  $candidates = [
    __DIR__ . '/../../frontend/dist',  // desarrollo local
    __DIR__ . '/../..',                 // producción (dist está en leeds/)
  ];

  $distPath = null;
  foreach ($candidates as $p) {
    if (file_exists($p . '/index.html')) {
      $distPath = $p;
      break;
    }
  }

  if (!$distPath) {
    http_response_code(500);
    echo 'Frontend build not found. Run pnpm build first.';
    exit;
  }

  $filePath = $distPath . $uri;

  if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeMap = [
      'html' => 'text/html',
      'css'  => 'text/css',
      'js'   => 'application/javascript',
      'json' => 'application/json',
      'png'  => 'image/png',
      'jpg'  => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'gif'  => 'image/gif',
      'svg'  => 'image/svg+xml',
      'ico'  => 'image/x-icon',
      'webp' => 'image/webp',
      'woff2' => 'font/woff2',
      'woff'  => 'font/woff',
      'ttf'   => 'font/ttf',
    ];
    $mime = $mimeMap[$ext] ?? mime_content_type($filePath) ?: 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($filePath);
    return;
  }

  readfile($distPath . '/index.html');
  return;
}

// ── API routes below ────────────────────────────────────────────

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../helpers.php';

spl_autoload_register(function (string $class) {
  $baseDir = __DIR__ . '/../';

  $prefixes = [
    'Core\\'       => 'Core/',
    'Models\\'     => 'Models/',
    'Controllers\\' => 'Controllers/',
  ];

  foreach ($prefixes as $prefix => $dir) {
    if (str_starts_with($class, $prefix)) {
      $relativeClass = substr($class, strlen($prefix));
      $file = $baseDir . $dir . str_replace('\\', '/', $relativeClass) . '.php';

      if (file_exists($file)) {
        require_once $file;
        return;
      }
    }
  }
});

use Core\Router;
use Controllers\AuthController;
use Controllers\DashboardController;
use Controllers\LeadController;
use Controllers\TemplateController;
use Controllers\TierController;
use Controllers\UserController;

$router = new Router();

$router->post('/api/auth/login', [AuthController::class, 'login'], false);
$router->get('/api/auth/me', [AuthController::class, 'me']);

$router->get('/api/dashboard', [DashboardController::class, 'index']);

$router->get('/api/leads', [LeadController::class, 'index']);
$router->post('/api/leads', [LeadController::class, 'store']);
$router->get('/api/leads/check-duplicate', [LeadController::class, 'checkDuplicate']);
$router->get('/api/leads/{id}', [LeadController::class, 'show']);
$router->put('/api/leads/{id}', [LeadController::class, 'update']);
$router->delete('/api/leads/{id}', [LeadController::class, 'destroy']);
$router->post('/api/leads/{id}/send', [LeadController::class, 'sendMessage']);

$router->get('/api/templates', [TemplateController::class, 'index']);
$router->post('/api/templates', [TemplateController::class, 'store']);
$router->get('/api/templates/{id}', [TemplateController::class, 'show']);
$router->put('/api/templates/{id}', [TemplateController::class, 'update']);
$router->delete('/api/templates/{id}', [TemplateController::class, 'destroy']);

$router->get('/api/tiers', [TierController::class, 'index']);
$router->post('/api/tiers', [TierController::class, 'store']);
$router->get('/api/tiers/{id}', [TierController::class, 'show']);
$router->put('/api/tiers/{id}', [TierController::class, 'update']);
$router->delete('/api/tiers/{id}', [TierController::class, 'destroy']);

$router->get('/api/users', [UserController::class, 'index']);
$router->post('/api/users', [UserController::class, 'store']);
$router->get('/api/users/{id}', [UserController::class, 'show']);
$router->put('/api/users/{id}', [UserController::class, 'update']);
$router->delete('/api/users/{id}', [UserController::class, 'destroy']);
$router->put('/api/users/{id}/apikey', [UserController::class, 'updateApikey']);

$router->dispatch();
