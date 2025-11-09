<?php
// Manual includes - all required dependencies
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Core/DB.php';
require_once __DIR__ . '/../../app/Models/BaseModel.php';
require_once __DIR__ . '/../../app/Models/FoodItem.php';
require_once __DIR__ . '/../../app/Models/Order.php';
require_once __DIR__ . '/../../app/Models/DeliveryDriver.php';
require_once __DIR__ . '/../../app/Models/Delivery.php';

// Initialize database connection
App\Core\DB::init([
    'host' => DB_HOST,
    'port' => DB_PORT,
    'dbname' => DB_NAME,
    'user' => DB_USER,
    'pass' => DB_PASS,
    'charset' => 'utf8mb4'
]);

echo "UNIT TEST: Stock Management\n";
echo "===========================\n";

$foodModel = new App\Models\FoodItem();
$orderModel = new App\Models\Order();

// Test multiple items with different quantities
$testItemsData = [
    ['id' => 1, 'name' => 'Margherita Pizza', 'qty' => 1],
    ['id' => 4, 'name' => 'Cola 500ml', 'qty' => 3]
];

echo "Testing stock management with multiple items:\n";

// Get original stock levels
$originalStocks = [];
$testItems = [];
$totalExpected = 0;

foreach ($testItemsData as $itemData) {
    $food = $foodModel->find($itemData['id']);
    if ($food) {
        $originalStocks[$itemData['id']] = $food['stock'];
        $unitPrice = (float)$food['price'];
        $discount = (int)$food['discount_percent'];
        $lineTotal = round($unitPrice * (1 - $discount / 100) * $itemData['qty'], 2);
        
        $testItems[] = [
            'id' => $itemData['id'],
            'qty' => $itemData['qty'],
            'price' => $unitPrice,
            'discount_percent' => $discount,
            'line_total' => $lineTotal
        ];
        
        $totalExpected += $lineTotal;
        
        echo "   - " . $food['name'] . ": Stock=" . $food['stock'] . ", Ordering=" . $itemData['qty'] . "\n";
    }
}

echo "\n1. Creating order with multiple items...\n";
try {
    $orderId = $orderModel->createOrder(5, $testItems, $totalExpected);
    echo "   SUCCESS: Order created: ID $orderId\n";
    
    echo "2. Verifying stock reductions...\n";
    $allPassed = true;
    
    foreach ($testItemsData as $itemData) {
        $updatedFood = $foodModel->find($itemData['id']);
        $expectedStock = $originalStocks[$itemData['id']] - $itemData['qty'];
        
        if ($updatedFood['stock'] == $expectedStock) {
            echo "   SUCCESS: " . $itemData['name'] . ": " . $updatedFood['stock'] . " (correctly reduced by " . $itemData['qty'] . ")\n";
        } else {
            echo "   FAILED: " . $itemData['name'] . ": " . $updatedFood['stock'] . " (expected " . $expectedStock . ")\n";
            $allPassed = false;
        }
    }
    
    if ($allPassed) {
        echo "\nSTOCK MANAGEMENT TEST: PASSED\n";
    } else {
        echo "\nSTOCK MANAGEMENT TEST: FAILED\n";
    }
    
} catch (Exception $e) {
    echo "FAILED: Test failed: " . $e->getMessage() . "\n";
}