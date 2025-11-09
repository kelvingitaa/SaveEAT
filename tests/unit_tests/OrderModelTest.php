<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\Order;
use App\Models\FoodItem;

echo "UNIT TEST: Order Model Methods\n";
echo "==============================\n\n";

// Test 1: Test order item calculation logic
echo "1. Testing order item calculation logic...\n";

$orderModel = new Order();
$foodModel = new FoodItem();

// Get a test food item to work with
$testFood = $foodModel->find(1); // Margherita Pizza
if (!$testFood) {
    die("Need seeded data. Run: php scripts/seed.php\n");
}

// Test the calculation logic that happens in your ConsumerController
$unitPrice = (float)$testFood['price'];
$discountPercent = (int)$testFood['discount_percent'];
$quantity = 2;

// This is the same logic from your ConsumerController::cart()
$finalPrice = round($unitPrice * (1 - $discountPercent / 100), 2);
$lineTotal = $finalPrice * $quantity;

echo "   Item: " . $testFood['name'] . "\n";
echo "   Price: $unitPrice, Discount: $discountPercent%\n";
echo "   Quantity: $quantity\n";
echo "   Final Price: $finalPrice\n";
echo "   Line Total: $lineTotal\n";

$expectedTotal = 1200 * 0.8 * 2; // 1200 -20% = 960 × 2 = 1920
if ($lineTotal == $expectedTotal) {
    echo "   SUCCESS: Calculation matches expected $expectedTotal\n\n";
} else {
    echo "   FAILED: Expected $expectedTotal, got $lineTotal\n\n";
}

// Test 2: Test order retrieval methods
echo "2. Testing order retrieval methods...\n";

$userOrders = $orderModel->byUser(5); // consumer@saveeat.com
if (is_array($userOrders)) {
    echo "   SUCCESS: Retrieved " . count($userOrders) . " orders for user\n";
    
    if (!empty($userOrders)) {
        $firstOrder = $userOrders[0];
        echo "   Order ID: " . $firstOrder['id'] . ", Total: " . $firstOrder['total_price'] . "\n";
        
        // Test getOrderItems method
        $items = $orderModel->getOrderItems($firstOrder['id']);
        echo "   Order items: " . count($items) . " items\n";
    }
} else {
    echo "   FAILED: Could not retrieve user orders\n";
}

echo "\nORDER MODEL TEST: COMPLETED\n";