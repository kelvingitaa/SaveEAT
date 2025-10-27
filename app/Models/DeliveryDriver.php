<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class DeliveryDriver extends BaseModel
{
    protected $table = 'delivery_drivers';

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM delivery_drivers WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateStatus(int $driverId, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE delivery_drivers SET status = :status WHERE id = :id");
        return $stmt->execute(['id' => $driverId, 'status' => $status]);
    }

    public function getAvailableDrivers(): array
    {
        $stmt = $this->db->prepare("
            SELECT dd.*, u.name, u.phone
            FROM delivery_drivers dd
            LEFT JOIN users u ON dd.user_id = u.id
            WHERE dd.status = 'available'
            ORDER BY u.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}