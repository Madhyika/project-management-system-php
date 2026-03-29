<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $title = null): never
    {
        render($view, $data, $title);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    protected function backTo(string $fallback): never
    {
        back_to($fallback);
    }

    protected function notFound(): never
    {
        abort_not_found();
    }
}
