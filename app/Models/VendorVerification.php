<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class VendorVerification extends BaseModel
{
    protected $table = 'vendor_verifications';

    public function findByVendorId(int $vendorId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM vendor_verifications WHERE vendor_id = :vendor_id LIMIT 1");
        $stmt->execute(['vendor_id' => $vendorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getPendingVerifications(): array
    {
        $stmt = $this->db->prepare("
            SELECT vv.*, v.business_name, v.location, u.email, u.name as owner_name 
            FROM vendor_verifications vv 
            LEFT JOIN vendors v ON vv.vendor_id = v.id 
            LEFT JOIN users u ON v.user_id = u.id 
            WHERE vv.verification_status = 'pending' 
            ORDER BY vv.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve(int $verificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE vendor_verifications 
            SET verification_status = 'approved', verified_by_admin_id = :admin_id, verified_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $verificationId, 'admin_id' => $adminId]);
    }

    public function reject(int $verificationId, int $adminId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE vendor_verifications 
            SET verification_status = 'rejected', verified_by_admin_id = :admin_id, verified_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $verificationId, 'admin_id' => $adminId]);
    }
    public function saveLicenseDocument(int $vendorId, array $file): bool
{
    $uploadDir = __DIR__ . '/../../public/uploads/verifications/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new \Exception('Only PDF, JPG, and PNG files are allowed');
    }
    
    if ($file['size'] > $maxSize) {
        throw new \Exception('File size must be less than 5MB');
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'vendor_' . $vendorId . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Save to database
        $stmt = $this->db->prepare("
            INSERT INTO vendor_verifications (vendor_id, license_document_path, verification_status, created_at)
            VALUES (:vendor_id, :document_path, 'pending', NOW())
            ON DUPLICATE KEY UPDATE license_document_path = :document_path, verification_status = 'pending'
        ");
        return $stmt->execute([
            'vendor_id' => $vendorId,
            'document_path' => '/uploads/verifications/' . $filename
        ]);
    }
    
    return false;
}
}