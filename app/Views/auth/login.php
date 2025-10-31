<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <h3 class="mb-3">Login</h3>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>/login">
      <input type="hidden" name="_csrf" value="<?= $token ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" name="remember_me" class="form-check-input" id="remember_me" value="1">
        <label class="form-check-label" for="remember_me">Remember me for 30 days</label>
      </div>
      <button class="btn btn-primary w-100">Login</button>
    </form>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/register">Don't have an account? Register</a>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';