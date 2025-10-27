<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card"></i> Complete Payment</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>Order Summary</h6>
                        <p class="mb-1">Order #<?= $order['id'] ?></p>
                        <p class="mb-1">Total Amount: <strong>KSh <?= number_format($order['total_price'], 2) ?></strong></p>
                    </div>

                    <form method="post" action="<?= BASE_URL ?>/payment/initiate">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="mobile_money" selected>Mobile Money (M-Pesa)</option>
                                <option value="card">Credit/Debit Card</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number (for M-Pesa)</label>
                            <input type="tel" name="phone_number" class="form-control" 
                                   placeholder="07XXXXXXXX" pattern="[0-9]{10}" required>
                            <small class="form-text text-muted">Enter your M-Pesa registered phone number</small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            You will receive a payment prompt on your phone. Please enter your M-Pesa PIN to complete the transaction.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-lock-fill"></i> Pay KSh <?= number_format($order['total_price'], 2) ?>
                            </button>
                            <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';