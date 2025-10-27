<?php
ob_start();
?>
<div class="text-center py-5">
  <h1>403</h1>
  <p>You are not authorized to access this page.</p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
