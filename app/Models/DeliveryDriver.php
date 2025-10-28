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
    return $stmt->execute(['status' => $status, 'id' => $driverId]);
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

    public function create(int $userId, array $data): int
{
    $stmt = $this->db->prepare('INSERT INTO delivery_drivers (user_id, vehicle_type, license_plate, license_file, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $userId,
        $data['vehicle_type'] ?? '',
        $data['license_plate'] ?? '',
        $data['license_file'] ?? null,
        'pending' // Drivers start as pending
    ]);
    return (int)$this->db->lastInsertId();
}

// In app/Models/Delivery.php - make sure these methods exist:
public function getDeliveriesByDriver(int $driverId): array
{
    $stmt = $this->db->prepare("
        SELECT d.*, o.total_price, u.name as customer_name, u.phone as customer_phone 
        FROM deliveries d 
        JOIN orders o ON d.order_id = o.id 
        JOIN users u ON o.user_id = u.id 
        WHERE d.driver_id = :driver_id 
        ORDER BY d.created_at DESC
    ");
    $stmt->execute(['driver_id' => $driverId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getAvailableDeliveries(): array
{
    $stmt = $this->db->prepare("
        SELECT d.*, o.total_price, u.name as customer_name, u.phone as customer_phone 
        FROM deliveries d 
        JOIN orders o ON d.order_id = o.id 
        JOIN users u ON o.user_id = u.id 
        WHERE d.driver_id IS NULL AND d.status = 'pending'
        ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function update(int $driverId, array $data): bool
{
    $allowedFields = ['vehicle_type', 'license_plate', 'status', 'current_location'];
    $updates = [];
    $params = ['id' => $driverId];
    
    foreach ($data as $key => $value) {
        if (in_array($key, $allowedFields)) {
            $updates[] = "$key = :$key";
            $params[$key] = $value;
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $sql = "UPDATE delivery_drivers SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
}
}