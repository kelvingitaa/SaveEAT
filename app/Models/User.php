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

    public function findByRememberToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.* FROM users u 
             WHERE u.remember_token = :token 
             AND u.remember_expires > NOW() 
             AND u.status = "active" 
             LIMIT 1'
        );
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateRememberToken(int $userId, ?string $token = null, ?string $expires = null): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET remember_token = :token, remember_expires = :expires WHERE id = :id'
        );
        return $stmt->execute([
            'token' => $token,
            'expires' => $expires,
            'id' => $userId
        ]);
    }

    public function setEmailVerified(int $userId, bool $verified = true): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET email_verified = :verified WHERE id = :id'
        );
        return $stmt->execute([
            'verified' => $verified ? 1 : 0,
            'id' => $userId
        ]);
    }

    public function isEmailVerified(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT email_verified FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['email_verified'] == 1;
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

    public function update(int $userId, array $data): bool
    {
        $allowedFields = ['name', 'email', 'password_hash', 'phone', 'address', 'status', 'two_factor_code', 'two_factor_expires', 'email_verified', 'remember_token', 'remember_expires'];
        $updates = [];
        $params = ['id' => $userId];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function setTwoFactorCode(int $userId, string $code)
    {
        error_log("💾 [USER MODEL] Setting 2FA code for user $userId: $code");
        
        $expires = date('Y-m-d H:i:s', time() + 600); // expires in 10 min
        $stmt = $this->db->prepare(
            "UPDATE users SET two_factor_code=?, two_factor_expires=? WHERE id=?"
        );
        
        $result = $stmt->execute([$code, $expires, $userId]);
        error_log("💾 [USER MODEL] Update result: " . ($result ? 'success' : 'failed'));
        
        return $result;
    }

    public function verifyTwoFactorCode(int $userId, string $code): bool
    {
        $stmt = $this->db->prepare(
            "SELECT two_factor_code, two_factor_expires FROM users WHERE id=?"
        );
        $stmt->execute([$userId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) return false;

        if (
            $data['two_factor_code'] === $code &&
            strtotime($data['two_factor_expires']) >= time()
        ) {
            // clear code after success
            $stmt2 = $this->db->prepare(
                "UPDATE users SET two_factor_code=NULL, two_factor_expires=NULL WHERE id=?"
            );
            $stmt2->execute([$userId]);
            return true;
        }

        return false;
    }

    public function find(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}