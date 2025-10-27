<?php
namespace App\Models;

use PDO;

class Vendor extends BaseModel
{
    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO vendors (user_id,business_name,location,contact_phone,logo_path,approved,created_at,updated_at) VALUES (:user_id,:business_name,:location,:contact_phone,:logo_path,0,NOW(),NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'business_name' => $data['business_name'] ?? '',
            'location' => $data['location'] ?? '',
            'contact_phone' => $data['contact_phone'] ?? '',
            'logo_path' => $data['logo_path'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
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
        $stmt = $this->db->prepare('UPDATE vendors SET approved = 1, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
    }
}
