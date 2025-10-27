<?php
ob_start();
?>
<div class="text-center">
  <h3>Order Successful</h3>
  <p>Your order #<?= (int)$order_id ?> has been placed. Total: KSh <?= number_format($total, 0) ?>.</p>
  <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-primary">View Orders</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';