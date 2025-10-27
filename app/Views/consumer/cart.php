<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<h3>Your Cart</h3>
<?php if (empty($items)): ?>
  <div class="alert alert-info">Your cart is empty.</div>
<?php else: ?>
<table class="table">
  <thead><tr><th>Item</th><th>Qty</th><th>Line Total</th></tr></thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= htmlspecialchars($it['name']) ?></td>
        <td><?= (int)$it['qty'] ?></td>
        <td>KSh <?= number_format($it['line_total'], 0) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<p class="text-end"><strong>Total: KSh <?= number_format($total, 0) ?></strong></p>
<form method="post" action="<?= BASE_URL ?>/consumer/checkout">
  <input type="hidden" name="_csrf" value="<?= $token ?>">
  <button class="btn btn-success">Checkout (Simulated)</button>
</form>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';