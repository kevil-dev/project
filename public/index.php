<?php
declare(strict_types=1);

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e): void {
    $logDir  = ROOT_PATH . '/storage/logs';
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';

    $logLine = '[' . date('Y-m-d H:i:s') . '] '
        . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine()
        . PHP_EOL;

    if (is_dir($logDir)) {
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    if (headers_sent()) {
        return;
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Something went wrong']);
});

$config = \App\Core\Config::getInstance();

$db = new \App\Core\Database(
    $config->get('db.host'),
    $config->get('db.username'),
    $config->get('db.password'),
    $config->get('db.database'),
);

$router = new \App\Core\Router($db);

require ROOT_PATH . '/routes/web.php';

$request  = new \App\Core\Request();
$response = $router->dispatch($request);
$response->send();
