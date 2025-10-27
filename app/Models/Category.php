<?php
namespace App\Models;

use PDO;

class Category extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
