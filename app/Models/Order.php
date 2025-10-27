<?php
namespace App\Models;

use PDO;

class Order extends BaseModel
{
    public function createOrder(int $consumerId, array $items, float $total): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO orders (consumer_id,total_price,status,created_at,updated_at) VALUES (:consumer_id,:total_price,:status,NOW(),NOW())');
            $stmt->execute([
                'consumer_id' => $consumerId,
                'total_price' => $total,
                'status' => 'paid',
            ]);
            $orderId = (int)$this->db->lastInsertId();

            $oi = $this->db->prepare('INSERT INTO order_items (order_id,food_item_id,quantity,unit_price,discount_percent,line_total) VALUES (:order_id,:food_item_id,:quantity,:unit_price,:discount_percent,:line_total)');
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

            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE consumer_id = :uid ORDER BY created_at DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
