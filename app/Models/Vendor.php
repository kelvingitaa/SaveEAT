<?php
namespace App\Models;

use PDO;

class Vendor extends BaseModel
{
    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, status, created_at, updated_at) VALUES (:user_id, :business_name, :location, :contact_phone, 0, "pending", NOW(), NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'business_name' => $data['business_name'] ?? '',
            'location' => $data['address'] ?? '', // Map address to location
            'contact_phone' => $data['phone'] ?? '',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM vendors WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function byUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM vendors WHERE user_id = :uid LIMIT 1');
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function approve(int $vendorId): void
    {
        $stmt = $this->db->prepare('UPDATE vendors SET approved = 1, status = "active", updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
    }

    public function update(int $vendorId, array $data): void
    {
        $allowedFields = ['business_name', 'location', 'contact_phone', 'approved', 'status'];
        $updates = [];
        $params = ['id' => $vendorId];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (!empty($updates)) {
            $updates[] = 'updated_at = NOW()';
            $sql = "UPDATE vendors SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
    }
}