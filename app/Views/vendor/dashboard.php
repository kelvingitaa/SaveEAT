<?php
ob_start();
?>
<h3>Vendor Dashboard</h3>
<?php if (!$vendor || !$vendor['approved']): ?>
  <div class="alert alert-warning">Your account is pending admin approval. You will be able to list items after approval.</div>
<?php else: ?>
  <p><a href="<?= BASE_URL ?>/vendor/items" class="btn btn-primary">Manage Items</a></p>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
