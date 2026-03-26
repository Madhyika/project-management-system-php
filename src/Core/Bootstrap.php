<?php

declare(strict_types=1);

namespace App\Core;

final class Bootstrap
{
    public function __construct(
        private readonly Environment $environment,
        private readonly SessionManager $sessionManager,
    ) {
    }

    public function boot(): void
    {
        $this->environment->load();
        $this->sessionManager->start();
    }
}
