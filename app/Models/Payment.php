<?php
namespace App\Models;

use App\Core\DB;
use PDO;

class Payment extends BaseModel
{
    protected $table = 'payments';

    public function createPayment(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO payments (order_id, amount, payment_method, transaction_id, payment_status, created_at)
            VALUES (:order_id, :amount, :payment_method, :transaction_id, :payment_status, NOW())
        ");
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function updatePaymentStatus(int $paymentId, string $status, string $transactionId = null): bool
    {
        $sql = "UPDATE payments SET payment_status = :status, paid_at = NOW()";
        $params = ['id' => $paymentId, 'status' => $status];
        
        if ($transactionId) {
            $sql .= ", transaction_id = :transaction_id";
            $params['transaction_id'] = $transactionId;
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getTotalRevenue(): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM payments 
            WHERE payment_status = 'completed'
        ");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }
}