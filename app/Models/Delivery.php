<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Delivery extends BaseModel
{
    protected $table = 'deliveries';

    public function assignDriver(int $orderId, int $driverId): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO deliveries (order_id, driver_id, status, created_at)
            VALUES (:order_id, :driver_id, 'assigned', NOW())
        ");
        return $stmt->execute([
            'order_id' => $orderId,
            'driver_id' => $driverId
        ]);
    }

    public function updateStatus(int $deliveryId, string $status): bool
    {
        $sql = "UPDATE deliveries SET status = :status";
        $params = ['id' => $deliveryId, 'status' => $status];
        
        if ($status === 'picked_up') {
            $sql .= ", pickup_time = NOW()";
        } elseif ($status === 'delivered') {
            $sql .= ", delivery_time = NOW()";
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getDeliveriesByDriver(int $driverId): array
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