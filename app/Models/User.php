<?php
namespace App\Models;

use PDO;

class User extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name,email,password_hash,role,status,created_at,updated_at) VALUES (:name,:email,:password_hash,:role,:status,NOW(),NOW())');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
        ]);
        return (int)$this->db->lastInsertId();
    }
}
