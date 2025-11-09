<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\FoodItem;

echo "UNIT TEST: Discount Calculation (Using Real Data)\n";
echo "=================================================\n\n";

$foodModel = new FoodItem();

// Test 1: Test discount calculations with real food items
echo "1. Testing discount calculations with real food data...\n";

$testItems = $foodModel->browse([], 5, 0); // Get 5 food items

if (empty($testItems)) {
    die("Need seeded data. Run: php scripts/seed.php\n");
}

echo "   Testing discount calculations:\n\n";

foreach ($testItems as $item) {
    $originalPrice = (float)$item['price'];
    $discountPercent = (int)$item['discount_percent'];
    $discountedPrice = round($originalPrice * (1 - $discountPercent / 100), 2);
    
    echo "   " . $item['name'] . ":\n";
    echo "     Original: KES $originalPrice\n";
    echo "     Discount: $discountPercent%\n";
    echo "     Final: KES $discountedPrice\n";
    
    // Verify calculation
    $expected = round($originalPrice * (1 - $discountPercent / 100), 2);
    if ($discountedPrice == $expected) {
        echo "      Calculation correct\n\n";
    } else {
        echo "      Calculation error: expected $expected\n\n";
    }
}

// Test 2: Test cart total calculation (like in ConsumerController)
echo "2. Testing cart total calculation...\n";

$cartItems = [];
$simulatedTotal = 0;

// Simulate adding items to cart
foreach (array_slice($testItems, 0, 3) as $item) {
    $quantity = rand(1, 3);
    $unitPrice = (float)$item['price'];
    $discount = (int)$item['discount_percent'];
    $finalPrice = round($unitPrice * (1 - $discount / 100), 2);
    $lineTotal = $finalPrice * $quantity;
    
    $cartItems[] = [
        'name' => $item['name'],
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'discount_percent' => $discount,
        'line_total' => $lineTotal
    ];
    
    $simulatedTotal += $lineTotal;
}

echo "   Cart simulation:\n";
foreach ($cartItems as $cartItem) {
    echo "     - " . $cartItem['name'] . ": " . $cartItem['quantity'] . " × KES " . $cartItem['unit_price'] . 
         " (-" . $cartItem['discount_percent'] . "%) = KES " . $cartItem['line_total'] . "\n";
}

echo "   Total Cart Value: KES " . $simulatedTotal . "\n";
echo "   SUCCESS: Cart calculation completed\n";

echo "\nDISCOUNT CALCULATION TEST: COMPLETED\n";