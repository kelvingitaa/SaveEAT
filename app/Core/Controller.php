<?php
namespace App\Core;

class Controller
{
    protected function view(string $template, array $params = []): void
    {
        echo View::render($template, $params);
    }

    protected function redirect(string $path): void
    {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }
}
