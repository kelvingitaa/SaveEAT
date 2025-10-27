<?php
ob_start();
?>
<div class="text-center py-5">
  <h1>404</h1>
  <p>Page not found.</p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
