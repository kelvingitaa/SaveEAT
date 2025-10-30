<?php
ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-success">
                <div class="card-header bg-success text-white text-center">
                    <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                    <h3 class="mb-0">Payment Successful!</h3>
                </div>
                <div class="card-body text-center">
                    <p class="lead">Thank you for your payment!</p>
                    <p>Your order #<?= $orderId ?> has been confirmed and is being processed.</p>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        You will receive updates about your delivery via SMS and email.
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/delivery/track/<?= $orderId ?>" class="btn btn-primary">
                            <i class="bi bi-geo-alt"></i> Track Your Delivery
                        </a>
                        <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-outline-secondary">
                            View Order History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';