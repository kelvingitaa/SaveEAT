<?php
require_once __DIR__ . '/../config/config.php';

// Manual autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
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

use App\Models\FoodItem;
use App\Core\DB;

try {
    DB::init([
        'host' => DB_HOST,
        'port' => DB_PORT,
        'dbname' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => 'utf8mb4'
    ]);
    
    echo "[" . date('Y-m-d H:i:s') . "] Starting food status update...\n";
    
    $foodModel = new FoodItem();
    
    // Update expired items
    $foodModel->updateExpiredItems();
    echo "✓ Updated expired items\n";
    
    // Get stats
    $expiringToday = $foodModel->getExpiringToday();
    $expiringIn24Hours = $foodModel->getItemsExpiringInHours(24);
    
    echo "✓ Items expiring today: " . count($expiringToday) . "\n";
    echo "✓ Items expiring in 24 hours: " . count($expiringIn24Hours) . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] Food status update completed!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}