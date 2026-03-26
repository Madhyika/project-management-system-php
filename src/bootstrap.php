<?php

declare(strict_types=1);

use App\Core\Bootstrap;
use App\Core\Environment;
use App\Core\SessionManager;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = BASE_PATH . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

require BASE_PATH . '/src/helpers.php';

$bootstrap = new Bootstrap(
    new Environment(BASE_PATH . '/.env'),
    new SessionManager(BASE_PATH . '/storage/sessions'),
);

$bootstrap->boot();
