<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\Delivery;
use App\Models\DeliveryDriver;

echo "UNIT TEST: Delivery Model Methods\n";
echo "=================================\n\n";

$deliveryModel = new Delivery();
$driverModel = new DeliveryDriver();

// Test 1: Test driver availability logic
echo "1. Testing driver availability logic...\n";

$availableDrivers = $driverModel->getAvailableDrivers();
echo "   Available drivers: " . count($availableDrivers) . "\n";

foreach ($availableDrivers as $driver) {
    echo "   - " . $driver['name'] . " (" . $driver['vehicle_type'] . ")\n";
}

echo "\n";

// Test 2: Test delivery status methods
echo "2. Testing delivery status methods...\n";

// Get an existing delivery to test with
$existingDeliveries = $deliveryModel->getAvailableDeliveries();
if (!empty($existingDeliveries)) {
    $testDelivery = $existingDeliveries[0];
    $deliveryId = $testDelivery['id'];
    
    echo "   Testing delivery ID: $deliveryId\n";
    
    // Test findByOrderId method
    $delivery = $deliveryModel->findByOrderId($testDelivery['order_id']);
    if ($delivery) {
        echo "   Delivery found for order: " . $delivery['status'] . "\n";
    }
    
    // Test status progression logic
    $currentStatus = $delivery['status'] ?? 'pending_assignment';
    $allowedStatuses = ['pending_assignment', 'assigned', 'vendor_confirmed', 'picked_up', 'in_transit', 'delivered', 'completed', 'cancelled'];
    
    echo "   Current status: $currentStatus\n";
    echo "   Allowed statuses: " . implode(', ', $allowedStatuses) . "\n";
    
    if (in_array($currentStatus, $allowedStatuses)) {
        echo "   SUCCESS: Status validation passed\n";
    } else {
        echo "   FAILED: Invalid status\n";
    }
} else {
    echo "   No deliveries found for testing\n";
}

echo "\nDELIVERY MODEL TEST: COMPLETED\n";