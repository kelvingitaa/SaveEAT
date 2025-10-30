<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Donation extends BaseModel
{
    protected $table = 'donations';

    public function getDonationsByVendor(int $vendorId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, s.shelter_name, s.location as shelter_location, 
                   fi.name as food_name, fi.expiry_date
            FROM donations d
            LEFT JOIN shelters s ON d.shelter_id = s.id
            LEFT JOIN food_items fi ON d.food_item_id = fi.id
            WHERE d.vendor_id = :vendor_id
            ORDER BY d.donation_date DESC, d.created_at DESC
        ");
        $stmt->execute(['vendor_id' => $vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDonationsByShelter(int $shelterId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, v.business_name, v.location as vendor_location,
                   fi.name as food_name, fi.expiry_date, fi.description
            FROM donations d
            LEFT JOIN vendors v ON d.vendor_id = v.id
            LEFT JOIN food_items fi ON d.food_item_id = fi.id
            WHERE d.shelter_id = :shelter_id
            ORDER BY d.donation_date DESC, d.created_at DESC
        ");
        $stmt->execute(['shelter_id' => $shelterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableDonations(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, v.business_name, v.location as vendor_location,
                   fi.name as food_name, fi.expiry_date, fi.description,
                   s.shelter_name
            FROM donations d
            LEFT JOIN vendors v ON d.vendor_id = v.id
            LEFT JOIN food_items fi ON d.food_item_id = fi.id
            LEFT JOIN shelters s ON d.shelter_id = s.id
            WHERE d.status = 'scheduled' AND d.donation_date >= CURDATE()
            ORDER BY d.donation_date ASC, fi.expiry_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $donationId, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE donations SET status = :status, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $donationId, 'status' => $status]);
    }

    public function getDonationStats(): array
    {
        // Total donations
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_donations,
                SUM(quantity) as total_items_donated,
                COUNT(DISTINCT vendor_id) as active_vendors,
                COUNT(DISTINCT shelter_id) as supported_shelters
            FROM donations 
            WHERE status IN ('completed', 'scheduled')
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Monthly donations
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(donation_date, '%Y-%m') as month,
                COUNT(*) as donation_count,
                SUM(quantity) as items_donated
            FROM donations 
            WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
            ORDER BY month DESC
        ");
        $stmt->execute();
        $monthlyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top donating vendors
        $stmt = $this->db->prepare("
            SELECT 
                v.business_name,
                COUNT(d.id) as donation_count,
                SUM(d.quantity) as total_items
            FROM donations d
            LEFT JOIN vendors v ON d.vendor_id = v.id
            WHERE d.status IN ('completed', 'scheduled')
            GROUP BY v.id, v.business_name
            ORDER BY total_items DESC
            LIMIT 5
        ");
        $stmt->execute();
        $topVendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'overall' => $stats,
            'monthly' => $monthlyStats,
            'top_vendors' => $topVendors
        ];
    }

    public function getRecentDonations(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.*,
                v.business_name,
                s.shelter_name,
                fi.name as food_name
            FROM donations d
            LEFT JOIN vendors v ON d.vendor_id = v.id
            LEFT JOIN shelters s ON d.shelter_id = s.id
            LEFT JOIN food_items fi ON d.food_item_id = fi.id
            ORDER BY d.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO donations (vendor_id, shelter_id, food_item_id, quantity, 
                                 donation_date, status, notes, created_at)
            VALUES (:vendor_id, :shelter_id, :food_item_id, :quantity, 
                   :donation_date, :status, :notes, NOW())
        ");
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
}