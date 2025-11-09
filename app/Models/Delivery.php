<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Delivery extends BaseModel
{
    protected $table = 'deliveries';

    /**
     * Create a pending delivery (when no drivers available)
     */
    public function createPendingDelivery(int $orderId): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO deliveries (order_id, status, created_at)
            VALUES (:order_id, 'pending_assignment', NOW())
        ");
        return $stmt->execute(['order_id' => $orderId]);
    }

    public function assignDriver(int $orderId, int $driverId): bool
    {
        // Check if delivery already exists
        $existing = $this->findByOrderId($orderId);
        
        if ($existing) {
            // Update existing delivery with driver
            $stmt = $this->db->prepare("
                UPDATE deliveries 
                SET driver_id = :driver_id, status = 'assigned', updated_at = NOW()
                WHERE order_id = :order_id
            ");
            return $stmt->execute([
                'order_id' => $orderId,
                'driver_id' => $driverId
            ]);
        } else {
            // Create new delivery assignment
            $stmt = $this->db->prepare("
                INSERT INTO deliveries (order_id, driver_id, status, created_at)
                VALUES (:order_id, :driver_id, 'assigned', NOW())
            ");
            return $stmt->execute([
                'order_id' => $orderId,
                'driver_id' => $driverId
            ]);
        }
    }

    public function updateStatus(int $deliveryId, string $status): bool
    {
        $allowedStatuses = ['pending_assignment', 'assigned', 'vendor_confirmed', 'picked_up', 'in_transit', 'delivered', 'completed', 'cancelled'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException('Invalid delivery status');
        }
        
        $sql = "UPDATE deliveries SET status = :status, updated_at = NOW()";
        $params = ['id' => $deliveryId, 'status' => $status];
        
        // Set timestamps for specific status changes
        if ($status === 'vendor_confirmed') {
            $sql .= ", vendor_confirmed_at = NOW()";
        } elseif ($status === 'picked_up') {
            $sql .= ", pickup_time = NOW()";
        } elseif ($status === 'delivered') {
            $sql .= ", delivery_time = NOW()";
        } elseif ($status === 'completed') {
            $sql .= ", completed_at = NOW()";
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get delivery status with detailed information
     */
    public function getDeliveryStatus(int $orderId): array
    {
        $delivery = $this->findByOrderId($orderId);
        
        if (!$delivery) {
            return [
                'status' => 'not_found',
                'message' => 'Delivery not found',
                'timeline' => []
            ];
        }

        // Build timeline based on status
        $timeline = [];
        $currentStatus = $delivery['status'];
        
        // Define all possible statuses in order
        $allStatuses = [
            'pending_assignment' => ['icon' => '⏳', 'label' => 'Waiting for driver assignment'],
            'assigned' => ['icon' => '👨‍💼', 'label' => 'Driver assigned'],
            'vendor_confirmed' => ['icon' => '🏪', 'label' => 'Vendor confirmed order'],
            'picked_up' => ['icon' => '📦', 'label' => 'Driver picked up order'],
            'in_transit' => ['icon' => '🚗', 'label' => 'On the way to you'],
            'delivered' => ['icon' => '✅', 'label' => 'Delivered'],
            'completed' => ['icon' => '🎉', 'label' => 'Order completed']
        ];

        foreach ($allStatuses as $status => $info) {
            $timeline[] = [
                'status' => $status,
                'label' => $info['label'],
                'icon' => $info['icon'],
                'active' => $status === $currentStatus,
                'completed' => array_search($status, array_keys($allStatuses)) <= array_search($currentStatus, array_keys($allStatuses))
            ];
        }

        return [
            'status' => $currentStatus,
            'delivery' => $delivery,
            'timeline' => $timeline,
            'message' => $allStatuses[$currentStatus]['label'] ?? 'Unknown status'
        ];
    }

  public function getDeliveriesByDriver(int $driverId): array
{
    $stmt = $this->db->prepare("
        SELECT d.*, o.total_price, u.name as customer_name, u.phone as customer_phone,
               u.address as delivery_address, o.status as order_status
        FROM deliveries d
        LEFT JOIN orders o ON d.order_id = o.id
        LEFT JOIN users u ON o.user_id = u.id
        WHERE d.driver_id = :driver_id 
        AND d.status IN ('assigned', 'vendor_confirmed', 'picked_up', 'in_transit')
        ORDER BY 
            CASE 
                WHEN d.status = 'vendor_confirmed' THEN 1
                WHEN d.status = 'picked_up' THEN 2
                WHEN d.status = 'in_transit' THEN 3
                ELSE 4
            END,
            d.created_at DESC
    ");
    $stmt->execute(['driver_id' => $driverId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getActiveDeliveriesByDriver(int $driverId): array
{
    $stmt = $this->db->prepare("
        SELECT d.*, o.total_price, o.status as order_status, 
               u.name as customer_name, u.phone as customer_phone,
               u.address as delivery_address
        FROM deliveries d
        LEFT JOIN orders o ON d.order_id = o.id
        LEFT JOIN users u ON o.user_id = u.id
        WHERE d.driver_id = :driver_id 
        AND d.status IN ('assigned', 'vendor_confirmed', 'picked_up', 'in_transit')
        AND o.status IN ('preparing', 'ready', 'paid')
        ORDER BY 
            CASE 
                WHEN d.status = 'vendor_confirmed' AND o.status = 'ready' THEN 1
                WHEN d.status = 'vendor_confirmed' THEN 2
                WHEN d.status = 'picked_up' THEN 3
                WHEN d.status = 'in_transit' THEN 4
                ELSE 5
            END,
            d.created_at ASC
    ");
    $stmt->execute(['driver_id' => $driverId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getCompletedDeliveriesByDriver(int $driverId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, o.total_price, u.name as customer_name, u.phone as customer_phone,
                   u.address as delivery_address
            FROM deliveries d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE d.driver_id = :driver_id AND d.status = 'delivered'
            ORDER BY d.delivery_time DESC
        ");
        $stmt->execute(['driver_id' => $driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDeliveriesByDriver(int $driverId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, o.total_price, u.name as customer_name, u.phone as customer_phone,
                   u.address as delivery_address
            FROM deliveries d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE d.driver_id = :driver_id
            ORDER BY d.created_at DESC
        ");
        $stmt->execute(['driver_id' => $driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableDeliveries(): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name as customer_name, u.phone, u.address
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN deliveries d ON o.id = d.order_id
            WHERE o.status = 'paid' AND d.id IS NULL
            ORDER BY o.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByOrderId(int $orderId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, dd.vehicle_type, dd.license_plate, u.name as driver_name, u.phone as driver_phone
            FROM deliveries d
            LEFT JOIN delivery_drivers dd ON d.driver_id = dd.id
            LEFT JOIN users u ON dd.user_id = u.id
            WHERE d.order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}