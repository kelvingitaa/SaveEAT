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
}