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
}
