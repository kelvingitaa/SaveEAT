<?php
require_once __DIR__ . '/../config/config.php';

// Create direct PDO connection for seeding
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "Database connected successfully!\n";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

echo "Seeding database...\n";

// Clear existing data (in reverse order due to foreign keys)
$pdo->exec("DELETE FROM order_items");
$pdo->exec("DELETE FROM orders");
$pdo->exec("DELETE FROM food_items");
$pdo->exec("DELETE FROM vendors");
$pdo->exec("DELETE FROM categories");
$pdo->exec("DELETE FROM users");

// Seed Users
$users = [
    ['System Admin', 'admin@saveeat.com', 'admin123', 'admin', 'active'],
    ['Pizza Hub Owner', 'vendor1@saveeat.com', 'vendor123', 'vendor', 'active'],
    ['Burger Joint Owner', 'vendor2@saveeat.com', 'vendor123', 'vendor', 'active'],
    ['John Consumer', 'consumer@saveeat.com', 'consumer123', 'consumer', 'active']
];

$userIds = [];
foreach ($users as $user) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $user[0], // name
        $user[1], // email
        password_hash($user[2], PASSWORD_DEFAULT), // password_hash
        $user[3], // role
        $user[4]  // status
    ]);
    $userIds[] = $pdo->lastInsertId();
    echo "✓ User created: {$user[1]}\n";
}

// Seed Vendors
$vendors = [
    [$userIds[1], 'Pizza Hub', 'Westlands Mall, Nairobi', '0700111222', true],
    [$userIds[2], 'Burger Joint', 'CBD Nairobi', '0700333444', true]
];

$vendorIds = [];
foreach ($vendors as $vendor) {
    $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($vendor);
    $vendorIds[] = $pdo->lastInsertId();
    echo "✓ Vendor created: {$vendor[1]}\n";
}

// Seed Categories
$categories = [
    ['Pizza', 'Various pizza types'],
    ['Burgers', 'Beef, chicken and veggie burgers'],
    ['Drinks', 'Soft drinks and beverages'],
    ['Desserts', 'Sweet treats and cakes']
];

$categoryIds = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute($category);
    $categoryIds[] = $pdo->lastInsertId();
    echo "✓ Category created: {$category[0]}\n";
}

// Seed Food Items
$foodItems = [
    [$vendorIds[0], $categoryIds[0], 'Margherita Pizza', 'Classic cheese and tomato pizza', 12.99, 10, date('Y-m-d', strtotime('+7 days')), 20, 'active'],
    [$vendorIds[0], $categoryIds[0], 'Pepperoni Pizza', 'Pepperoni and cheese pizza', 14.99, 15, date('Y-m-d', strtotime('+5 days')), 15, 'active'],
    [$vendorIds[1], $categoryIds[1], 'Beef Burger', 'Juicy beef burger with fries', 8.99, 5, date('Y-m-d', strtotime('+3 days')), 25, 'active'],
    [$vendorIds[1], $categoryIds[1], 'Chicken Burger', 'Crispy chicken burger', 7.99, 0, date('Y-m-d', strtotime('+4 days')), 18, 'active'],
    [$vendorIds[0], $categoryIds[2], 'Cola', '500ml bottle', 2.99, 0, date('Y-m-d', strtotime('+30 days')), 50, 'active'],
    [$vendorIds[1], $categoryIds[3], 'Chocolate Cake', 'Rich chocolate cake slice', 4.99, 20, date('Y-m-d', strtotime('+2 days')), 10, 'active']
];

foreach ($foodItems as $item) {
    $stmt = $pdo->prepare("INSERT INTO food_items (vendor_id, category_id, name, description, price, discount_percent, expiry_date, stock, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($item);
    echo "✓ Food item created: {$item[2]}\n";
}

echo "Seeding completed! Database is now populated with sample data.\n";
echo "You can now login with:\n";
echo "- Admin: admin@saveeat.com / admin123\n";
echo "- Vendor: vendor1@saveeat.com / vendor123\n";
echo "- Consumer: consumer@saveeat.com / consumer123\n";