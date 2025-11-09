<?php
// Manual includes - all required dependencies
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Core/DB.php';
require_once __DIR__ . '/../../app/Models/BaseModel.php';
require_once __DIR__ . '/../../app/Models/Order.php';
require_once __DIR__ . '/../../app/Models/DeliveryDriver.php';
require_once __DIR__ . '/../../app/Models/Delivery.php';
require_once __DIR__ . '/../../app/Models/FoodItem.php';

// Initialize database connection
App\Core\DB::init([
    'host' => DB_HOST,
    'port' => DB_PORT,
    'dbname' => DB_NAME,
    'user' => DB_USER,
    'pass' => DB_PASS,
    'charset' => 'utf8mb4'
]);

echo "UNIT TEST: Automatic Driver Assignment\n";
echo "======================================\n";

$orderModel = new App\Models\Order();
$driverModel = new App\Models\DeliveryDriver();
$deliveryModel = new App\Models\Delivery();

// Get available drivers before test
echo "1. Checking available drivers...\n";
$availableDrivers = $driverModel->getAvailableDrivers();
echo "   Available drivers: " . count($availableDrivers) . "\n";
foreach ($availableDrivers as $driver) {
    echo "     - " . $driver['name'] . " (" . $driver['vehicle_type'] . ")\n";
}

// Create a test order
echo "\n2. Creating test order for driver assignment...\n";
$testItems = [
    [
        'id' => 6, // Beef Burger Combo
        'qty' => 1,
        'price' => 650,
        'discount_percent' => 10,
        'line_total' => 585
    ]
];

try {
    $orderId = $orderModel->createOrder(5, $testItems, 585);
    echo "   SUCCESS: Order created: ID $orderId\n";
    
    // Check if driver was assigned
    echo "3. Checking driver assignment...\n";
    $delivery = $deliveryModel->findByOrderId($orderId);
    
    if ($delivery) {
        echo "   SUCCESS: Delivery record created\n";
        echo "   Delivery status: " . $delivery['status'] . "\n";
        
        if ($delivery['driver_id']) {
            $driver = $driverModel->findByUserId($delivery['driver_id']);
            if ($driver) {
                echo "   SUCCESS: Driver assigned: " . $driver['name'] . "\n";
            } else {
                echo "   SUCCESS: Driver assigned (ID: " . $delivery['driver_id'] . ")\n";
            }
            echo "   AUTOMATIC DRIVER ASSIGNMENT: WORKING\n";
        } else {
            echo "   INFO: No driver assigned (status: " . $delivery['status'] . ")\n";
        }
    } else {
        echo "   FAILED: No delivery record created\n";
    }
    
} catch (Exception $e) {
    echo "FAILED: Test failed: " . $e->getMessage() . "\n";
}