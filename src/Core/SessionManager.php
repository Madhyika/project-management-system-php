<?php

declare(strict_types=1);

namespace App\Core;

final class SessionManager
{
    public function __construct(
        private readonly string $sessionPath,
    ) {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $useCustomSessionPath = is_dir($this->sessionPath) || @mkdir($this->sessionPath, 0777, true);

        if ($useCustomSessionPath && !is_writable($this->sessionPath)) {
            $useCustomSessionPath = @chmod($this->sessionPath, 0777) && is_writable($this->sessionPath);
        }

        if ($useCustomSessionPath) {
            session_save_path($this->sessionPath);
        }

        session_start();
    }
}
