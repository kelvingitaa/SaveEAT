<?php
use App\Core\Session;

$success = Session::flash('success');
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <!-- Success Card -->
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-lg text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    
                    <!-- Main Message -->
                    <h2 class="text-success mb-3">Order Confirmed!</h2>
                    <p class="lead text-muted mb-4">
                        Thank you for your order. We're preparing your food and will notify you when it's ready.
                    </p>

                    <!-- Order Details Card -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-receipt"></i> Order Summary
                            </h5>
                            
                            <div class="row text-start">
                                <div class="col-6">
                                    <strong>Order Number:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    #<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?>
                                </div>
                            </div>
                            
                            <div class="row text-start mt-2">
                                <div class="col-6">
                                    <strong>Total Amount:</strong>
                                </div>
                                <div class="col-6 text-end text-success fw-bold">
                                    KSh <?= number_format($total, 0) ?>
                                </div>
                            </div>
                            
                            <div class="row text-start mt-2">
                                <div class="col-6">
                                    <strong>Payment Method:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-phone"></i> Mobile Money
                                    </span>
                                </div>
                            </div>
                            
                            <div class="row text-start mt-2">
                                <div class="col-6">
                                    <strong>Order Status:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="badge bg-info">
                                        <i class="bi bi-credit-card"></i> Payment Confirmed
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="bi bi-clock-history"></i> What happens next?
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            <li><i class="bi bi-check text-success"></i> Vendor notified and preparing your order</li>
                            <li><i class="bi bi-clock text-warning"></i> Expected preparation time: 20-30 minutes</li>
                            <li><i class="bi bi-truck text-primary"></i> Delivery driver will be assigned shortly</li>
                            <li><i class="bi bi-phone text-success"></i> You'll receive SMS updates on your order status</li>
                        </ul>
                    </div>

                    <!-- Contact Information -->
                    <div class="card border-warning mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-warning">
                                <i class="bi bi-info-circle"></i> Need Help?
                            </h6>
                            <p class="small mb-2">
                                If you have any questions about your order, contact our support team:
                            </p>
                            <div class="row small">
                                <div class="col-6">
                                    <i class="bi bi-telephone"></i> <strong>Support:</strong><br>
                                    <a href="tel:+254700000000">0700 000 000</a>
                                </div>
                                <div class="col-6">
                                    <i class="bi bi-whatsapp"></i> <strong>WhatsApp:</strong><br>
                                    <a href="https://wa.me/254700000000">0700 000 000</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-primary btn-lg">
                            <i class="bi bi-receipt"></i> View My Orders
                        </a>
                        <a href="<?= BASE_URL ?>/consumer" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-bag"></i> Continue Shopping
                        </a>
                    </div>

                    <!-- Quick Links -->
                    <div class="mt-4">
                        <small class="text-muted">
                            You can also:
                            <a href="<?= BASE_URL ?>/consumer/orders" class="text-decoration-none">Track your order</a> • 
                            <a href="<?= BASE_URL ?>/consumer" class="text-decoration-none">Browse more food</a>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Food Safety Reminder -->
            <div class="card mt-4 border-success">
                <div class="card-body text-center py-3">
                    <small class="text-success">
                        <i class="bi bi-shield-check"></i> 
                        <strong>Food Safety Guarantee:</strong> All our vendors follow strict food safety standards. 
                        Your food will be delivered fresh and safe to eat.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}
.bg-success {
    background-color: #28a745 !important;
}
.alert {
    border-radius: 10px;
}
.btn {
    border-radius: 8px;
}
</style>

<script>
// Optional: Add some celebratory effects
document.addEventListener('DOMContentLoaded', function() {
    // You could add confetti or other celebration effects here
    console.log('Order completed successfully! Order ID: <?= $order_id ?>');
    
    // Example: Track conversion in analytics
    // gtag('event', 'purchase', {
    //     transaction_id: '<?= $order_id ?>',
    //     value: <?= $total ?>
    // });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';