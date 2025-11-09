<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\FoodItem;
use App\Models\Category;

echo "UNIT TEST: FoodItem Model Methods\n";
echo "=================================\n\n";

$foodModel = new FoodItem();
$categoryModel = new Category();

// Test 1: Test food item browsing and filtering
echo "1. Testing food item browsing logic...\n";

// Test the browse method with different filters
$filters = [
    [] // No filters - get all
];

foreach ($filters as $filter) {
    $items = $foodModel->browse($filter, 10, 0);
    echo "   Found " . count($items) . " items with filters: " . json_encode($filter) . "\n";
    
    if (!empty($items)) {
        $firstItem = $items[0];
        echo "   Sample: " . $firstItem['name'] . " - KES " . $firstItem['price'] . "\n";
    }
}

echo "\n";

// Test 2: Test individual food item methods
echo "2. Testing individual food item methods...\n";

$testFood = $foodModel->find(1); // Get first food item
if ($testFood) {
    echo "   Item: " . $testFood['name'] . "\n";
    echo "   Price: " . $testFood['price'] . "\n";
    echo "   Discount: " . $testFood['discount_percent'] . "%\n";
    echo "   Stock: " . $testFood['stock'] . "\n";
    echo "   Status: " . $testFood['status'] . "\n";
    
    // Test discount calculation
    $discountedPrice = $testFood['price'] * (1 - $testFood['discount_percent'] / 100);
    echo "   Discounted Price: " . round($discountedPrice, 2) . "\n";
    
    echo "   SUCCESS: Food item data retrieved and processed\n";
} else {
    echo "   FAILED: Could not retrieve food item\n";
}

echo "\nFOODITEM MODEL TEST: COMPLETED\n";