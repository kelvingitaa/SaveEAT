<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/config.php';

// Manual autoloader for scripts
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

use App\Core\DB;

// Initialize database connection
try {
    DB::init([
        'host' => DB_HOST,
        'port' => DB_PORT,
        'dbname' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => 'utf8mb4'
    ]);
    echo "Database connected successfully!\n";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$migrations = [
    // Your existing tables...
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin','vendor','consumer','shelter','driver') NOT NULL,  
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        status ENUM('active','suspended','pending') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS vendors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        business_name VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        contact_phone VARCHAR(20) NOT NULL,
        approved BOOLEAN DEFAULT FALSE,
        status ENUM('active','suspended','pending') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    // NEW: Vendor Verification System
    "CREATE TABLE IF NOT EXISTS vendor_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT NOT NULL,
        license_number VARCHAR(255),
        license_document_path VARCHAR(500),
        verified_by_admin_id INT,
        verification_status ENUM('pending','approved','rejected') DEFAULT 'pending',
        verified_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
        FOREIGN KEY (verified_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
    )",

    "CREATE TABLE IF NOT EXISTS food_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT NOT NULL,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        discount_percent INT DEFAULT 0,
        expiry_date DATE NOT NULL,
        stock INT DEFAULT 0,
        image_path VARCHAR(500),
        status ENUM('active','inactive','sold_out','expired') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )",

    // NEW: Shelter Management
    "CREATE TABLE IF NOT EXISTS shelters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        shelter_name VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        contact_phone VARCHAR(20) NOT NULL,
        capacity INT,
        verification_document_path VARCHAR(500),
        verified BOOLEAN DEFAULT FALSE,
        status ENUM('active','suspended','pending') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending','paid','preparing','ready','completed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        food_item_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        discount_percent INT DEFAULT 0,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
    )",

    // NEW: Payment System
    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('mobile_money','card','cash') DEFAULT 'mobile_money',
        transaction_id VARCHAR(255),
        payment_status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
        paid_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )",

    // NEW: Delivery System
    "CREATE TABLE IF NOT EXISTS delivery_drivers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        vehicle_type VARCHAR(100),
        license_plate VARCHAR(50),
        status ENUM('available','busy','offline') DEFAULT 'offline',
        current_location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS deliveries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        driver_id INT,
        pickup_time TIMESTAMP NULL,
        delivery_time TIMESTAMP NULL,
        status ENUM('pending','assigned','picked_up','in_transit','delivered','cancelled') DEFAULT 'pending',
        delivery_address TEXT,
        customer_phone VARCHAR(20),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (driver_id) REFERENCES delivery_drivers(id) ON DELETE SET NULL
    )",

    // NEW: Food Donations
    "CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT NOT NULL,
        shelter_id INT NOT NULL,
        food_item_id INT NOT NULL,
        quantity INT NOT NULL,
        donation_date DATE NOT NULL,
        status ENUM('pending','scheduled','completed','cancelled') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
        FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE CASCADE,
        FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
    )"
];

echo "Running migrations...\n";

foreach ($migrations as $i => $sql) {
    try {
        DB::pdo()->exec($sql);
        echo "✓ Migration " . ($i + 1) . " executed successfully\n";
    } catch (Exception $e) {
        echo "✗ Migration " . ($i + 1) . " failed: " . $e->getMessage() . "\n";
    }
}

echo "Migrations completed!\n";