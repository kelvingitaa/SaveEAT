<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Shelter extends BaseModel
{
    protected $table = 'shelters';

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM shelters WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ADD THESE METHODS:
    public function getShelterStats(int $shelterId): array
    {
        // Pending requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as pending_requests 
            FROM donations 
            WHERE shelter_id = ? AND status = 'pending'
        ");
        $stmt->execute([$shelterId]);
        $pending = $stmt->fetch(PDO::FETCH_ASSOC);

        // Completed donations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as completed_donations,
                   SUM(quantity) as total_items_received
            FROM donations 
            WHERE shelter_id = ? AND status = 'completed'
        ");
        $stmt->execute([$shelterId]);
        $completed = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'pending_requests' => $pending['pending_requests'] ?? 0,
            'completed_donations' => $completed['completed_donations'] ?? 0,
            'total_items_received' => $completed['total_items_received'] ?? 0
        ];
    }

    public function getAvailableDonationsForShelter(int $shelterId): array
    {
        $stmt = $this->db->prepare("
            SELECT fi.*, v.business_name, v.location as vendor_location,
                   TIMESTAMPDIFF(HOUR, NOW(), fi.expiry_date) as hours_until_expiry
            FROM food_items fi
            LEFT JOIN vendors v ON fi.vendor_id = v.id
            WHERE fi.status = 'active' 
            AND fi.expiry_date >= CURDATE()
            AND fi.stock > 0
            AND fi.vendor_id IN (
                SELECT id FROM vendors WHERE status = 'active' AND approved = 1
            )
            ORDER BY fi.expiry_date ASC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Keep your existing methods:
    public function getPendingVerifications(): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.email, u.name as contact_person 
            FROM shelters s 
            LEFT JOIN users u ON s.user_id = u.id 
            WHERE s.verified = 0 
            ORDER BY s.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve(int $shelterId, int $adminId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE shelters 
            SET verified = 1, status = 'active', updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $shelterId]);
    }

    public function getActiveShelters(): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.email, u.phone 
            FROM shelters s 
            LEFT JOIN users u ON s.user_id = u.id 
            WHERE s.verified = 1 AND s.status = 'active'
            ORDER BY s.shelter_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function update(int $id, array $data): bool
{
    $fields = [];
    $values = [];
    
    foreach ($data as $field => $value) {
        $fields[] = "$field = ?";
        $values[] = $value;
    }
    
    $values[] = $id;
    $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($values);
}
}