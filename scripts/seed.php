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
    ['Suspended Vendor', 'suspended@saveeat.com', 'vendor123', 'vendor', 'suspended'],
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

// First, check if vendors table has status column
$hasStatusColumn = false;
try {
    $result = $pdo->query("SHOW COLUMNS FROM vendors LIKE 'status'");
    $hasStatusColumn = $result->rowCount() > 0;
} catch (Exception $e) {
    echo "Note: Checking status column failed: " . $e->getMessage() . "\n";
}

// Seed Vendors - handle both with and without status column
$vendors = [
    [$userIds[1], 'Pizza Hub', 'Westlands Mall, Nairobi', '0700111222', true],
    [$userIds[2], 'Burger Joint', 'CBD Nairobi', '0700333444', true],
    [$userIds[3], 'Suspended Restaurant', 'Karen, Nairobi', '0700555666', true]
];

$vendorIds = [];
foreach ($vendors as $vendor) {
    if ($hasStatusColumn) {
        // Determine status based on vendor data
        $status = ($vendor[4] && $vendor[1] !== 'Suspended Restaurant') ? 'active' : 'suspended';
        $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $vendor[0], // user_id
            $vendor[1], // business_name
            $vendor[2], // location
            $vendor[3], // contact_phone
            $vendor[4], // approved
            $status      // status
        ]);
    } else {
        // Fallback without status column
        $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $vendor[0], // user_id
            $vendor[1], // business_name
            $vendor[2], // location
            $vendor[3], // contact_phone
            $vendor[4]  // approved
        ]);
    }
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

// Seed Food Items with REALISTIC Kenyan prices and images
$foodItems = [
    // Format: [vendor_id, category_id, name, description, price, discount_percent, expiry_date, stock, image_path, status]
    [$vendorIds[0], $categoryIds[0], 'Margherita Pizza', 'Classic cheese and tomato pizza', 1200, 20, date('Y-m-d', strtotime('+7 days')), 20, 'https://tse2.mm.bing.net/th/id/OIP.PY5zQl0PhEtHkwXxfS-lNQHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[0], $categoryIds[0], 'Pepperoni Pizza', 'Pepperoni and cheese pizza', 1350, 15, date('Y-m-d', strtotime('+5 days')), 15, 'https://th.bing.com/th/id/OIP.6xgy-h0Gwc7s9oRf0J3rkAHaJ1?o=7rm=3&rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[1], $categoryIds[1], 'Beef Burger', 'Juicy beef burger with fries', 650, 10, date('Y-m-d', strtotime('+3 days')), 25, 'https://tse3.mm.bing.net/th/id/OIP.RVuQtEcNiscLNfRjkhT0wwHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[1], $categoryIds[1], 'Chicken Burger', 'Crispy chicken burger with fries', 580, 10, date('Y-m-d', strtotime('+4 days')), 18, 'https://tse4.mm.bing.net/th/id/OIP.m93SV6ox1swkqRaBnEzbHgHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[0], $categoryIds[2], 'Cola', '500ml bottle', 180, 10, date('Y-m-d', strtotime('+30 days')), 50, 'https://tse2.mm.bing.net/th/id/OIP.PUvKv1v0P9wqOa8oQVYqFAHaE8?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[1], $categoryIds[3], 'Chocolate Cake', 'Rich chocolate cake slice', 450, 20, date('Y-m-d', strtotime('+2 days')), 10, 'https://charlotteslivelykitchen.com/wp-content/uploads/2019/01/chocolate-cake-1.jpg', 'active']
];

foreach ($foodItems as $item) {
    $stmt = $pdo->prepare("INSERT INTO food_items (vendor_id, category_id, name, description, price, discount_percent, expiry_date, stock, image_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($item);
    echo "✓ Food item created: {$item[2]}\n";
}

echo "Seeding completed! Database is now populated with sample data.\n";
echo "You can now login with:\n";
echo "- Admin: admin@saveeat.com / admin123\n";
echo "- Vendor: vendor1@saveeat.com / vendor123\n";
echo "- Consumer: consumer@saveeat.com / consumer123\n";
echo "- Suspended Vendor: suspended@saveeat.com / vendor123\n";

// If status column was missing, inform the user
if (!$hasStatusColumn) {
    echo "\n⚠️  Note: Vendors table is missing the 'status' column.\n";
    echo "   Please run the migration again to add it: php scripts/migrate.php\n";
}