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
$tables = ['order_items', 'orders', 'food_items', 'vendor_verifications', 'vendors', 'shelters', 'categories', 'users'];
foreach ($tables as $table) {
    $pdo->exec("DELETE FROM $table");
}

// Seed Users
$users = [
    ['System Admin', 'admin@saveeat.com', 'admin123', 'admin', 'active'],
    ['Pizza Hub Owner', 'vendor1@saveeat.com', 'vendor123', 'vendor', 'active'],
    ['Burger Joint Owner', 'vendor2@saveeat.com', 'vendor123', 'vendor', 'active'],
    ['Suspended Vendor', 'suspended@saveeat.com', 'vendor123', 'vendor', 'suspended'],
    ['John Consumer', 'consumer@saveeat.com', 'consumer123', 'consumer', 'active'],
    ['Hope Shelter Manager', 'hope@shelter.com', 'shelter123', 'shelter', 'active'],
    ['Grace Home Manager', 'grace@shelter.com', 'shelter123', 'shelter', 'active']
];

$userIds = [];
foreach ($users as $user) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $user[0], $user[1], password_hash($user[2], PASSWORD_DEFAULT), $user[3], $user[4]
    ]);
    $userIds[] = $pdo->lastInsertId();
    echo "✓ User created: {$user[1]}\n";
}

// Seed Vendors
$vendors = [
    [$userIds[1], 'Pizza Hub', 'Westlands Mall, Nairobi', '0700111222', true, 'active'],
    [$userIds[2], 'Burger Joint', 'CBD Nairobi', '0700333444', true, 'active'],
    [$userIds[3], 'Suspended Restaurant', 'Karen, Nairobi', '0700555666', true, 'suspended']
];

$vendorIds = [];
foreach ($vendors as $vendor) {
    $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($vendor);
    $vendorIds[] = $pdo->lastInsertId();
    echo "✓ Vendor created: {$vendor[1]}\n";
}

// Seed Vendor Verifications
foreach ($vendorIds as $vendorId) {
    $stmt = $pdo->prepare("INSERT INTO vendor_verifications (vendor_id, license_number, license_document_path, verification_status, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $vendorId,
        'LIC' . str_pad($vendorId, 6, '0', STR_PAD_LEFT),
        '/documents/license_' . $vendorId . '.pdf',
        'approved'
    ]);
    echo "✓ Vendor verification created for vendor ID: $vendorId\n";
}

// Seed Shelters
$shelters = [
    [$userIds[5], 'Hope Shelter', 'Nairobi CBD', '0700444555', 50, '/documents/hope_certificate.pdf', true, 'active'],
    [$userIds[6], 'Grace Home', 'Westlands', '0700666777', 30, '/documents/grace_certificate.pdf', true, 'active']
];

$shelterIds = [];
foreach ($shelters as $shelter) {
    $stmt = $pdo->prepare("INSERT INTO shelters (user_id, shelter_name, location, contact_phone, capacity, verification_document_path, verified, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($shelter);
    $shelterIds[] = $pdo->lastInsertId();
    echo "✓ Shelter created: {$shelter[1]}\n";
}

// Seed Categories -
$categories = [
    ['Pizza', 'Various pizza types'],
    ['Burgers', 'Beef, chicken and veggie burgers'],
    ['Drinks', 'Soft drinks and beverages'],
    ['Desserts', 'Sweet treats and cakes']
];

$categoryIds = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())"); // ADDED updated_at
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
echo "- Hope Shelter: hope@shelter.com / shelter123\n";
echo "- Grace Home: grace@shelter.com / shelter123\n";