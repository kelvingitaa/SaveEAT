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
$tables = ['donations', 'deliveries', 'delivery_drivers', 'payments', 'order_items', 'orders', 'food_items', 'vendor_verifications', 'vendors', 'shelters', 'categories', 'users'];
foreach ($tables as $table) {
    try {
        $pdo->exec("DELETE FROM $table");
        echo "✓ Cleared table: $table\n";
    } catch (Exception $e) {
        echo "⚠ Could not clear $table: " . $e->getMessage() . "\n";
    }
}

// Reset auto-increment counters
$resetTables = ['users', 'categories', 'vendors', 'vendor_verifications', 'food_items', 'shelters', 'orders', 'order_items', 'payments', 'delivery_drivers', 'deliveries', 'donations'];
foreach ($resetTables as $table) {
    try {
        $pdo->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
    } catch (Exception $e) {
        // Ignore errors for tables that might not exist yet
    }
}

// Seed Users
$users = [
    ['System Admin', 'admin@saveeat.com', 'admin123', 'admin', 'active'],
    ['Pizza Hub Owner', 'vendor1@saveeat.com', 'vendor123', 'vendor', 'active'],
    ['Burger Joint Owner', 'vendor2@saveeat.com', 'vendor123', 'vendor', 'active'],
    ['Suspended Vendor', 'suspended@saveeat.com', 'vendor123', 'vendor', 'suspended'],
    ['John Consumer', 'consumer@saveeat.com', 'consumer123', 'consumer', 'active'],
    ['Hope Shelter Manager', 'hope@shelter.com', 'shelter123', 'shelter', 'active'],
    ['Grace Home Manager', 'grace@shelter.com', 'shelter123', 'shelter', 'active'],
    ['Delivery Driver 1', 'driver1@saveeat.com', 'driver123', 'driver', 'active'],
    ['Mary Consumer', 'mary@consumer.com', 'consumer123', 'consumer', 'active'] // Added for orders
];

$userIds = [];
foreach ($users as $user) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, phone, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $user[0], 
        $user[1], 
        password_hash($user[2], PASSWORD_DEFAULT), 
        $user[3], 
        $user[4],
        '0712345678', // Default phone
        '123 Main Street, Nairobi' // Default address
    ]);
    $userIds[] = $pdo->lastInsertId();
    echo "✓ User created: {$user[1]} (ID: {$userIds[count($userIds)-1]})\n";
}

// Seed Vendors
$vendors = [
    [$userIds[1], 'Pizza Hub', 'Westlands Mall, Nairobi', '0700111222', true, 'active'],
    [$userIds[2], 'Burger Joint', 'CBD Nairobi, Moi Avenue', '0700333444', true, 'active'],
    [$userIds[3], 'Suspended Restaurant', 'Karen, Nairobi', '0700555666', true, 'suspended']
];

$vendorIds = [];
foreach ($vendors as $vendor) {
    $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($vendor);
    $vendorIds[] = $pdo->lastInsertId();
    echo "✓ Vendor created: {$vendor[1]} (ID: {$vendorIds[count($vendorIds)-1]})\n";
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
    [$userIds[5], 'Hope Shelter', 'Nairobi CBD, Tom Mboya Street', '0700444555', 50, '/documents/hope_certificate.pdf', true, 'active'],
    [$userIds[6], 'Grace Home', 'Westlands, Rhapta Road', '0700666777', 30, '/documents/grace_certificate.pdf', true, 'active']
];

$shelterIds = [];
foreach ($shelters as $shelter) {
    $stmt = $pdo->prepare("INSERT INTO shelters (user_id, shelter_name, location, contact_phone, capacity, verification_document_path, verified, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute($shelter);
    $shelterIds[] = $pdo->lastInsertId();
    echo "✓ Shelter created: {$shelter[1]} (ID: {$shelterIds[count($shelterIds)-1]})\n";
}

// Seed Categories
$categories = [
    ['Pizza', 'Various pizza types and flavors'],
    ['Burgers', 'Beef, chicken and veggie burgers with fries'],
    ['Drinks', 'Soft drinks, juices and beverages'],
    ['Desserts', 'Sweet treats, cakes and pastries'],
    ['African Dishes', 'Traditional Kenyan and African meals'],
    ['Fast Food', 'Quick meals and snacks']
];

$categoryIds = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute($category);
    $categoryIds[] = $pdo->lastInsertId();
    echo "✓ Category created: {$category[0]} (ID: {$categoryIds[count($categoryIds)-1]})\n";
}

// Seed Food Items with REALISTIC Kenyan prices and images
$foodItems = [
    // Format: [vendor_id, category_id, name, description, storage_instructions, price, discount_percent, expiry_date, stock, image_path, status]
    
    // Pizza Hub Items
    [$vendorIds[0], $categoryIds[0], 'Margherita Pizza', 'Classic cheese and tomato pizza', 'Keep refrigerated. Consume within 2 hours of delivery.', 1200, 20, date('Y-m-d', strtotime('+1 days')), 8, 'https://cdn.loveandlemons.com/wp-content/uploads/2019/09/margherita-pizza.jpg', 'active'],
    [$vendorIds[0], $categoryIds[0], 'Pepperoni Pizza', 'Pepperoni and cheese pizza', 'Store in cool place. Best consumed warm.', 1350, 15, date('Y-m-d', strtotime('+1 days')), 6, 'https://th.bing.com/th/id/OIP.6xgy-h0Gwc7s9oRf0J3rkAHaJ1?pid=ImgDetMain', 'active'],
    [$vendorIds[0], $categoryIds[0], 'BBQ Chicken Pizza', 'Grilled chicken with BBQ sauce', 'Refrigerate immediately. Reheat before serving.', 1450, 25, date('Y-m-d', strtotime('+1 days')), 4, 'https://bing.com/th?id=OSK.8e89cd16586b4abd80baf45edd0184b2', 'active'],
    [$vendorIds[0], $categoryIds[2], 'Cola 500ml', 'Cold refreshing cola', 'Keep cool. Serve chilled.', 180, 10, date('Y-m-d', strtotime('+30 days')), 50, 'https://tse2.mm.bing.net/th/id/OIP.PUvKv1v0P9wqOa8oQVYqFAHaE8?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[0], $categoryIds[2], 'Fresh Orange Juice', 'Freshly squeezed orange juice', 'Keep refrigerated. Consume within 24 hours.', 250, 0, date('Y-m-d', strtotime('+1 days')), 12, 'https://tse4.mm.bing.net/th/id/OIP.7Eu2KnbFi7gRo9kRcxB13QHaLH?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    
    // Burger Joint Items
    [$vendorIds[1], $categoryIds[1], 'Beef Burger Combo', 'Juicy beef burger with fries and drink', 'Serve immediately. Keep fries crispy.', 650, 10, date('Y-m-d', strtotime('+1 days')), 15, 'https://tse3.mm.bing.net/th/id/OIP.RVuQtEcNiscLNfRjkhT0wwHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[1], $categoryIds[1], 'Chicken Burger Combo', 'Crispy chicken burger with fries', 'Consume within 3 hours. Keep refrigerated if storing.', 580, 15, date('Y-m-d', strtotime('+1 days')), 12, 'https://www.kitchensanctuary.com/wp-content/uploads/2019/08/Crispy-Chicken-Burger-square-FS-4518.jpg', 'active'],
    [$vendorIds[1], $categoryIds[1], 'Veggie Burger', 'Plant-based burger with fresh vegetables', 'Store in cool place. Best served fresh.', 520, 20, date('Y-m-d', strtotime('+1 days')), 8, 'https://www.vegrecipesofindia.com/wp-content/uploads/2020/12/burger-recipe-1.jpg', 'active'],
    [$vendorIds[1], $categoryIds[3], 'Chocolate Cake Slice', 'Rich chocolate cake slice', 'Keep refrigerated. Consume within 24 hours.', 450, 20, date('Y-m-d', strtotime('+1 days')), 10, 'https://tse4.mm.bing.net/th/id/OIP.j1azJBT4F2bPJK0BgW1z0wHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[1], $categoryIds[3], 'Apple Pie', 'Homemade apple pie slice', 'Store at room temperature. Warm before serving.', 380, 15, date('Y-m-d', strtotime('+2 days')), 6, 'https://tse4.mm.bing.net/th/id/OIP.Kg9xrjdtjST5bA2tO3QjfwHaLH?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    
    // Additional items for variety
    [$vendorIds[0], $categoryIds[4], 'Nyama Choma', 'Grilled meat with kachumbari', 'Serve warm. Consume immediately for best taste.', 800, 30, date('Y-m-d', strtotime('+1 days')), 5, 'https://tse4.mm.bing.net/th/id/OIP.mqF-8nNgxxKsDG7-JiN5mQHaHa?w=1600&h=1600&rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[1], $categoryIds[5], 'Chips Masala', 'Crispy fries with masala spices', 'Serve hot and crispy. Do not refrigerate.', 350, 25, date('Y-m-d', strtotime('+1 days')), 20, 'https://tse1.mm.bing.net/th/id/OIP._Ih8VDdzy2vArhu2eCKBowHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 'active'],
    [$vendorIds[0], $categoryIds[4], 'Ugali & Sukuma', 'Traditional meal with collard greens', 'Keep covered. Reheat before serving.', 450, 10, date('Y-m-d', strtotime('+1 days')), 7, 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=400', 'active']
];

foreach ($foodItems as $index => $item) {
    try {
        $stmt = $pdo->prepare("INSERT INTO food_items (vendor_id, category_id, name, description, storage_instructions, price, discount_percent, expiry_date, stock, image_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute($item);
        echo "✓ Food item created: {$item[2]} (Stock: {$item[8]})\n";
    } catch (Exception $e) {
        // If storage_instructions column doesn't exist, insert without it
        if (strpos($e->getMessage(), 'storage_instructions') !== false) {
            $stmt = $pdo->prepare("INSERT INTO food_items (vendor_id, category_id, name, description, price, discount_percent, expiry_date, stock, image_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$item[0], $item[1], $item[2], $item[3], $item[5], $item[6], $item[7], $item[8], $item[9], $item[10]]);
            echo "✓ Food item created (without storage): {$item[2]} (Stock: {$item[8]})\n";
        } else {
            echo "✗ Failed to create food item {$item[2]}: " . $e->getMessage() . "\n";
        }
    }
}

// Seed Delivery Driver - UPDATED: Added license_file column
try {
    $driverStmt = $pdo->prepare("INSERT INTO delivery_drivers (user_id, vehicle_type, license_plate, license_file, status, current_location, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $driverStmt->execute([
        $userIds[7], 
        'Motorcycle', 
        'KCR 123A', 
        '/documents/driver_license_1.pdf', // ADDED: license_file path
        'available', 
        'Nairobi CBD'
    ]);
    $driverId = $pdo->lastInsertId();
    echo "✓ Delivery driver created (ID: $driverId) with license file\n";
} catch (Exception $e) {
    echo "⚠ Could not create delivery driver: " . $e->getMessage() . "\n";
}

// ========== SAMPLE ORDERS AND DELIVERIES ==========
echo "\nCreating sample orders and deliveries...\n";

// Create sample orders
$orders = [
    [$userIds[8], 2450.00, 'paid'], // Mary Consumer
    [$userIds[8], 1800.00, 'paid'],
    [$userIds[8], 3200.00, 'paid'],
    [$userIds[4], 1500.00, 'paid']  // John Consumer
];

$orderIds = [];
foreach ($orders as $order) {
    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $orderStmt->execute($order);
    $orderIds[] = $pdo->lastInsertId();
    echo "✓ Sample order created: #{$pdo->lastInsertId()}\n";
}

// Create sample order items
$orderItems = [
    [$orderIds[0], 1, 2, 1200, 20, 1920], // 2x Margherita Pizza
    [$orderIds[0], 4, 1, 180, 10, 162],   // 1x Cola
    [$orderIds[1], 6, 2, 650, 10, 1170],  // 2x Beef Burger Combo
    [$orderIds[1], 9, 1, 450, 20, 360],   // 1x Chocolate Cake
    [$orderIds[2], 3, 1, 1450, 25, 1087.50], // 1x BBQ Chicken Pizza
    [$orderIds[2], 12, 2, 350, 25, 525],  // 2x Chips Masala
    [$orderIds[3], 7, 1, 580, 15, 493]    // 1x Chicken Burger Combo
];

foreach ($orderItems as $item) {
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, food_item_id, quantity, unit_price, discount_percent, line_total) VALUES (?, ?, ?, ?, ?, ?)");
    $itemStmt->execute($item);
    echo "✓ Order item created for order #{$item[0]}\n";
}

// Create sample deliveries
$deliveries = [
    // Assigned to driver
    [$orderIds[0], $driverId, 'assigned', '123 Main Street, Westlands, Nairobi', '0712345678', 'Please call upon arrival'],
    // Available for assignment (no driver)
    [$orderIds[1], null, 'pending', '456 Kimathi Street, CBD, Nairobi', '0723456789', 'Gate 5, Apartment 12'],
    [$orderIds[2], null, 'pending', '789 Moi Avenue, Nairobi', '0734567890', 'Office building, 3rd floor'],
    // Another assigned delivery
    [$orderIds[3], $driverId, 'picked_up', '321 Koinange Street, Nairobi', '0745678901', 'Leave with security']
];

foreach ($deliveries as $delivery) {
    $deliveryStmt = $pdo->prepare("INSERT INTO deliveries (order_id, driver_id, status, delivery_address, customer_phone, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $deliveryStmt->execute($delivery);
    echo "✓ Delivery created for order #{$delivery[0]} (Status: {$delivery[2]})\n";
}

// Create sample payments
$payments = [
    [$orderIds[0], 2082.00, 'mobile_money', 'MPESA_001', 'completed', date('Y-m-d H:i:s')],
    [$orderIds[1], 1530.00, 'mobile_money', 'MPESA_002', 'completed', date('Y-m-d H:i:s')],
    [$orderIds[2], 1612.50, 'mobile_money', 'MPESA_003', 'completed', date('Y-m-d H:i:s')],
    [$orderIds[3], 493.00, 'mobile_money', 'MPESA_004', 'completed', date('Y-m-d H:i:s')]
];

foreach ($payments as $payment) {
    $paymentStmt = $pdo->prepare("INSERT INTO payments (order_id, amount, payment_method, transaction_id, payment_status, paid_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $paymentStmt->execute($payment);
    echo "✓ Payment created for order #{$payment[0]}\n";
}

echo "\n🎉 SEEDING COMPLETED! Database is now populated with FULL sample data.\n";
echo "===============================================\n";
echo " LOGIN CREDENTIALS:\n";
echo " Admin: admin@saveeat.com / admin123\n";
echo " Vendor 1: vendor1@saveeat.com / vendor123 (Pizza Hub)\n";
echo " Vendor 2: vendor2@saveeat.com / vendor123 (Burger Joint)\n";
echo " Consumer: consumer@saveeat.com / consumer123\n";
echo " Mary Consumer: mary@consumer.com / consumer123 (Has orders)\n";
echo " Suspended Vendor: suspended@saveeat.com / vendor123\n";
echo " Hope Shelter: hope@shelter.com / shelter123\n";
echo " Grace Home: grace@shelter.com / shelter123\n";
echo " Delivery Driver: driver1@saveeat.com / driver123\n";
echo "===============================================\n";
echo " DRIVER DASHBOARD FEATURES:\n";
echo "✓ 2 assigned deliveries (1 assigned, 1 picked up)\n";
echo "✓ 2 available deliveries for assignment\n";
echo "✓ Online/offline status toggle\n";
echo "✓ Delivery status updates (picked up → in transit → delivered)\n";
echo "✓ License file properly stored in database\n";
echo "===============================================\n";
echo " DATABASE READY: All tables including license_file column are properly seeded!\n";