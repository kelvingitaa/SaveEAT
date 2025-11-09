<?php
// Manual includes - all required dependencies
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Core/DB.php';
require_once __DIR__ . '/../../app/Models/BaseModel.php';
require_once __DIR__ . '/../../app/Models/Order.php';
require_once __DIR__ . '/../../app/Models/FoodItem.php';
require_once __DIR__ . '/../../app/Models/DeliveryDriver.php';
require_once __DIR__ . '/../../app/Models/Delivery.php';

// Initialize database connection
try {
    App\Core\DB::init([
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

echo "UNIT TEST: Order Creation\n";
echo "=========================\n";

$orderModel = new App\Models\Order();
$foodModel = new App\Models\FoodItem();

// Get test data from seeded database
$testUserId = 5; // consumer@saveeat.com
$testFoodItem = $foodModel->find(1); // Margherita Pizza

if (!$testFoodItem) {
    die("TEST SETUP FAILED: Food item not found. Please run php scripts/seed.php first.\n");
}

echo "Testing with food item: " . $testFoodItem['name'] . "\n";
echo "Original price: " . $testFoodItem['price'] . ", Discount: " . $testFoodItem['discount_percent'] . "%\n";
echo "Original stock: " . $testFoodItem['stock'] . "\n\n";

// Calculate expected values
$unitPrice = (float)$testFoodItem['price'];
$discountPercent = (int)$testFoodItem['discount_percent'];
$quantity = 2;
$lineTotal = round($unitPrice * (1 - $discountPercent / 100) * $quantity, 2);
$expectedTotal = $lineTotal;

$testItems = [
    [
        'id' => $testFoodItem['id'],
        'qty' => $quantity,
        'price' => $unitPrice,
        'discount_percent' => $discountPercent,
        'line_total' => $lineTotal
    ]
];

try {
    echo "1. Creating order...\n";
    $orderId = $orderModel->createOrder($testUserId, $testItems, $expectedTotal);
    echo "   SUCCESS: Order created! ID: $orderId\n";
    
    // Verify order exists
    echo "2. Verifying order in database...\n";
    $order = $orderModel->find($orderId);
    if ($order && $order['total_price'] == $expectedTotal) {
        echo "   SUCCESS: Order total verified: KES " . $order['total_price'] . "\n";
        echo "   SUCCESS: Order status: " . $order['status'] . "\n";
    } else {
        echo "   FAILED: Order verification failed\n";
    }
    
    // Verify order items
    echo "3. Checking order items...\n";
    $items = $orderModel->getOrderItems($orderId);
    echo "   SUCCESS: Order items count: " . count($items) . "\n";
    foreach ($items as $item) {
        echo "     - " . $item['food_name'] . ": " . $item['quantity'] . " x KES " . $item['unit_price'] . " = KES " . $item['line_total'] . "\n";
    }
    
    // Verify stock reduction
    echo "4. Verifying stock reduction...\n";
    $updatedFood = $foodModel->find($testFoodItem['id']);
    $expectedStock = $testFoodItem['stock'] - $quantity;
    if ($updatedFood['stock'] == $expectedStock) {
        echo "   SUCCESS: Stock correctly reduced: " . $updatedFood['stock'] . " (was " . $testFoodItem['stock'] . ")\n";
    } else {
        echo "   FAILED: Stock reduction failed: " . $updatedFood['stock'] . " (expected " . $expectedStock . ")\n";
    }
    
    echo "\nORDER CREATION TEST: PASSED\n";
    
} catch (Exception $e) {
    echo "FAILED: Order creation failed: " . $e->getMessage() . "\n";
}