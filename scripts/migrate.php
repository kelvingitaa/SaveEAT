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
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$migrations = [
    // Users table
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

    // Categories table
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  
    )",

    // Vendors table
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

    // Vendor Verification System
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

    // Food Items table
    "CREATE TABLE IF NOT EXISTS food_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT NOT NULL,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        storage_instructions TEXT,
        price DECIMAL(10,2) NOT NULL,
        discount_percent INT DEFAULT 0,
        expiry_date DATE NOT NULL,
        stock INT DEFAULT 0,
        image_path VARCHAR(500),
        status ENUM('active','inactive','sold_out','expired','expiring_soon') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )",

    // Shelter Management
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

    // Orders table
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending','paid','preparing','ready','completed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    // Order Items table
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        food_item_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        discount_percent INT DEFAULT 0,
        line_total DECIMAL(10,2) NOT NULL, 
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
    )",

    // Payment System
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

    // Delivery System
    "CREATE TABLE IF NOT EXISTS delivery_drivers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        vehicle_type VARCHAR(100),
        license_plate VARCHAR(50),
        license_file VARCHAR(500),
        status ENUM('pending','available','busy','offline') DEFAULT 'pending', 
        current_location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    // Deliveries table
    "CREATE TABLE IF NOT EXISTS deliveries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        driver_id INT,
        pickup_time TIMESTAMP NULL,
        delivery_time TIMESTAMP NULL,
        vendor_confirmed_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        status ENUM('pending','pending_assignment','assigned','vendor_confirmed','picked_up','in_transit','delivered','completed','cancelled') DEFAULT 'pending',
        delivery_address TEXT,
        customer_phone VARCHAR(20),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (driver_id) REFERENCES delivery_drivers(id) ON DELETE SET NULL
    )",

    // Food Donations
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
        FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
        FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE CASCADE,
        FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
    )"
];

foreach ($migrations as $sql) {
    try {
        DB::pdo()->exec($sql);
    } catch (Exception $e) {
        // Continue with next migration even if one fails
    }
}

// Add storage_instructions column if it doesn't exist
try {
    $checkSql = "SHOW COLUMNS FROM food_items LIKE 'storage_instructions'";
    $result = DB::pdo()->query($checkSql);
    if ($result->rowCount() === 0) {
        DB::pdo()->exec("ALTER TABLE food_items ADD COLUMN storage_instructions TEXT AFTER description");
    }
} catch (Exception $e) {}

// Add line_total column if it doesn't exist
try {
    $checkSql = "SHOW COLUMNS FROM order_items LIKE 'line_total'";
    $result = DB::pdo()->query($checkSql);
    if ($result->rowCount() === 0) {
        DB::pdo()->exec("ALTER TABLE order_items ADD COLUMN line_total DECIMAL(10,2) AFTER discount_percent");
    }
} catch (Exception $e) {}

// Update deliveries table for real-time tracking
try {
    $checkSql = "SHOW COLUMNS FROM deliveries LIKE 'updated_at'";
    $result = DB::pdo()->query($checkSql);
    if ($result->rowCount() === 0) {
        DB::pdo()->exec("ALTER TABLE deliveries ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
} catch (Exception $e) {}

try {
    $checkSql = "SHOW COLUMNS FROM deliveries LIKE 'vendor_confirmed_at'";
    $result = DB::pdo()->query($checkSql);
    if ($result->rowCount() === 0) {
        DB::pdo()->exec("ALTER TABLE deliveries ADD COLUMN vendor_confirmed_at TIMESTAMP NULL");
    }
} catch (Exception $e) {}

try {
    $checkSql = "SHOW COLUMNS FROM deliveries LIKE 'completed_at'";
    $result = DB::pdo()->query($checkSql);
    if ($result->rowCount() === 0) {
        DB::pdo()->exec("ALTER TABLE deliveries ADD COLUMN completed_at TIMESTAMP NULL");
    }
} catch (Exception $e) {}

try {
    DB::pdo()->exec("ALTER TABLE deliveries MODIFY COLUMN status ENUM('pending','pending_assignment','assigned','vendor_confirmed','picked_up','in_transit','delivered','completed','cancelled') DEFAULT 'pending'");
} catch (Exception $e) {}

echo "Migrations completed successfully!\n";