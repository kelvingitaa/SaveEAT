<?php
// Load environment variables
$env_path = __DIR__ . '/../.env';
if (!file_exists($env_path)) {
    die('Error: .env file not found. Please create it from .env.example');
}

$lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
            $value = $matches[1];
        }
        
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

// Validate that required environment variables exist
$required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($required as $key) {
    if (!getenv($key)) {
        die("Error: Required environment variable $key is missing");
    }
}

// Database configuration - ONLY from environment variables
define('DB_HOST', getenv('DB_HOST'));
define('DB_PORT', getenv('DB_PORT'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));