<?php
namespace App\Models;

use PDO;

class Category extends BaseModel
{
    protected $table = 'categories';

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $query = "INSERT INTO categories (name, description, created_at, updated_at) 
                  VALUES (:name, :description, NOW(), NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $query = "UPDATE categories 
                  SET name = :name, description = :description, updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}