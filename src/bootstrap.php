<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/helpers.php';

load_env(BASE_PATH . '/.env');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = BASE_PATH . '/storage/sessions';

    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }

    session_save_path($sessionPath);
    session_start();
}
