<?php
namespace App\Core;

class View
{
    public static function render(string $template, array $params = []): string
    {
        $templatePath = APP_PATH . '/Views/' . $template . '.php';
        if (!file_exists($templatePath)) {
            return "View not found: $template";
        }
        extract($params, EXTR_OVERWRITE);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
