<?php
namespace App\Models;

use PDO;

class FoodItem extends BaseModel
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO food_items (vendor_id,category_id,name,description,price,discount_percent,expiry_date,stock,image_path,status,created_at,updated_at) VALUES (:vendor_id,:category_id,:name,:description,:price,:discount_percent,:expiry_date,:stock,:image_path,:status,NOW(),NOW())';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'vendor_id' => $data['vendor_id'],
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'expiry_date' => $data['expiry_date'],
            'stock' => $data['stock'] ?? 0,
            'image_path' => $data['image_path'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT fi.*, c.name as category_name FROM food_items fi LEFT JOIN categories c ON c.id = fi.category_id WHERE fi.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function browse(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $w = ['fi.status = "active"', 'fi.stock > 0'];
        $p = [];
        if (!empty($filters['category_id'])) { $w[] = 'fi.category_id = :category_id'; $p['category_id'] = $filters['category_id']; }
        if (!empty($filters['q'])) { $w[] = 'fi.name LIKE :q'; $p['q'] = '%' . $filters['q'] . '%'; }
        $sql = 'SELECT fi.*, c.name as category_name FROM food_items fi LEFT JOIN categories c ON c.id = fi.category_id WHERE ' . implode(' AND ', $w) . ' ORDER BY fi.created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($p as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getExpiringItems(int $vendorId): array
{
    $stmt = $this->db->prepare("
        SELECT * FROM food_items 
        WHERE vendor_id = :vendor_id 
        AND status = 'active'
        AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        AND stock > 0
        ORDER BY expiry_date ASC
    ");
    $stmt->execute(['vendor_id' => $vendorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getAvailableItemsForDonation(int $vendorId): array
{
    $stmt = $this->db->prepare("
        SELECT * FROM food_items 
        WHERE vendor_id = :vendor_id 
        AND status = 'active'
        AND stock > 0
        AND expiry_date >= CURDATE()
        ORDER BY expiry_date ASC
    ");
    $stmt->execute(['vendor_id' => $vendorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function deductStock(int $foodItemId, int $quantity): bool
{
    $stmt = $this->db->prepare("
        UPDATE food_items 
        SET stock = stock - :quantity,
            updated_at = NOW()
        WHERE id = :id AND stock >= :quantity
    ");
    return $stmt->execute([
        'id' => $foodItemId,
        'quantity' => $quantity
    ]);
}
public function enforce24HourRule(array $foodData): bool
{
    $expiryDate = $foodData['expiry_date'] ?? null;
    if (!$expiryDate) {
        return false;
    }
    
    try {
        // Calculate hours until expiry
        $expiryDateTime = new \DateTime($expiryDate);
        $currentDateTime = new \DateTime();
        $hoursUntilExpiry = ($expiryDateTime->getTimestamp() - $currentDateTime->getTimestamp()) / 3600;
        
        // Food must be safe to eat for at least 24 hours (expires in more than 24 hours)
        return $hoursUntilExpiry >= 24;
    } catch (\Exception $e) {
        return false;
    }
}

public function updateExpiredItems(): void
{
    // Update items that have expired to 'expired' status
    $stmt = $this->db->prepare("
        UPDATE food_items 
        SET status = 'expired', updated_at = NOW()
        WHERE expiry_date <= CURDATE() 
        AND status IN ('active', 'inactive', 'expiring_soon')
    ");
    $stmt->execute();
    
    // Update items expiring in next 24 hours to show warning
    $stmt = $this->db->prepare("
        UPDATE food_items 
        SET status = 'expiring_soon', updated_at = NOW()
        WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 24 HOUR)
        AND expiry_date > CURDATE()
        AND status = 'active'
    ");
    $stmt->execute();
}

public function getExpiringToday(): array
{
    $stmt = $this->db->prepare("
        SELECT fi.*, v.business_name, v.location
        FROM food_items fi
        LEFT JOIN vendors v ON fi.vendor_id = v.id
        WHERE fi.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 24 HOUR)
        AND fi.expiry_date > CURDATE()
        AND fi.status IN ('active', 'expiring_soon')
        AND fi.stock > 0
        ORDER BY fi.expiry_date ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getItemsExpiringInHours(int $hours = 24): array
{
    $stmt = $this->db->prepare("
        SELECT fi.*, v.business_name, v.location
        FROM food_items fi
        LEFT JOIN vendors v ON fi.vendor_id = v.id
        WHERE fi.expiry_date <= DATE_ADD(CURDATE(), INTERVAL :hours HOUR)
        AND fi.expiry_date > CURDATE()
        AND fi.status = 'active'
        AND fi.stock > 0
        ORDER BY fi.expiry_date ASC
    ");
    $stmt->execute(['hours' => $hours]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
