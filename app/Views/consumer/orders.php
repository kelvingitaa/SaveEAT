<?php
ob_start();
?>
<h3>My Orders</h3>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= (int)$o['id'] ?></td>
      <td>$<?= number_format($o['total_price'], 2) ?></td>
      <td><?= htmlspecialchars($o['status']) ?></td>
      <td><?= htmlspecialchars($o['created_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
