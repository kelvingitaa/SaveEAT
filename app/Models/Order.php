<?php
namespace App\Models;

use PDO;

class Order extends BaseModel
{
    public function createOrder(int $userId, array $items, float $total): int
    {
        $this->db->beginTransaction();
        try {
            // Create order with initial status
            $stmt = $this->db->prepare('INSERT INTO orders (user_id, total_price, status, created_at, updated_at) VALUES (:user_id, :total_price, :status, NOW(), NOW())');
            $stmt->execute([
                'user_id' => $userId, 
                'total_price' => $total,
                'status' => 'paid',
            ]);
            $orderId = (int)$this->db->lastInsertId();

            // Insert order items and update stock
            $oi = $this->db->prepare('INSERT INTO order_items (order_id, food_item_id, quantity, unit_price, discount_percent, line_total) VALUES (:order_id, :food_item_id, :quantity, :unit_price, :discount_percent, :line_total)');
            $upd = $this->db->prepare('UPDATE food_items SET stock = stock - :qty WHERE id = :id AND stock >= :qty');

            foreach ($items as $it) {
                $oi->execute([
                    'order_id' => $orderId,
                    'food_item_id' => $it['id'],
                    'quantity' => $it['qty'],
                    'unit_price' => $it['price'],
                    'discount_percent' => $it['discount_percent'] ?? 0,
                    'line_total' => $it['line_total'],
                ]);
                $upd->execute(['qty' => $it['qty'], 'id' => $it['id']]);
                if ($upd->rowCount() === 0) {
                    throw new \RuntimeException('Insufficient stock for item ' . $it['id']);
                }
            }

            // AUTOMATIC DRIVER ASSIGNMENT
            $this->assignDriverAutomatically($orderId);

            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Automatically assign an available driver to the order
     */
    private function assignDriverAutomatically(int $orderId): bool
    {
        $deliveryDriverModel = new DeliveryDriver();
        $deliveryModel = new Delivery();
        
        // Get available drivers
        $availableDrivers = $deliveryDriverModel->getAvailableDrivers();
        
        if (empty($availableDrivers)) {
            // No drivers available, create delivery without driver for manual assignment later
            return $deliveryModel->createPendingDelivery($orderId);
        }
        
        // Assign first available driver (you could add more sophisticated logic here)
        $driver = $availableDrivers[0];
        
        // Create delivery assignment
        return $deliveryModel->assignDriver($orderId, $driver['id']);
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare('
            SELECT oi.*, fi.name as food_name, fi.image_path, fi.description
            FROM order_items oi 
            LEFT JOIN food_items fi ON oi.food_item_id = fi.id 
            WHERE oi.order_id = :order_id
        ');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}