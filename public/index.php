<?php
declare(strict_types=1);
date_default_timezone_set('Africa/Nairobi'); // or your timezone
// Front controller
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/config.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Core\DB;
use App\Core\Session;
use App\Core\Auth;

Session::start();

DB::init([
    'host' => DB_HOST,
    'port' => DB_PORT,
    'dbname' => DB_NAME,
    'user' => DB_USER,
    'pass' => DB_PASS,
    'charset' => 'utf8mb4'
]);

$router = new Router(BASE_URL);
require APP_PATH . '/routes.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);