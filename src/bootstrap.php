<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/helpers.php';

load_env(BASE_PATH . '/.env');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = BASE_PATH . '/storage/sessions';
    $useCustomSessionPath = is_dir($sessionPath) || @mkdir($sessionPath, 0777, true);

    if ($useCustomSessionPath && !is_writable($sessionPath)) {
        $useCustomSessionPath = @chmod($sessionPath, 0777) && is_writable($sessionPath);
    }

    if ($useCustomSessionPath) {
        session_save_path($sessionPath);
    }

    session_start();
}
