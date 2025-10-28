<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h3 class="mb-3">Register</h3>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>/register/consumer">
      <input type="hidden" name="_csrf" value="<?= $token ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="consumer">Consumer</option>
            <option value="vendor">Vendor</option>
          </select>
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-success">Create Account</button>
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
