<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <h3 class="mb-3">Verify Your Email</h3>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    
    <?php if (!empty($debug_code)): ?>
    <div class="alert alert-warning">
        <strong>DEBUG MODE:</strong> Your verification code is: <strong><?= htmlspecialchars($debug_code) ?></strong>
    </div>
    <?php endif; ?>
    
    <div class="alert alert-info">
      <p>We sent a 6-digit verification code to <strong><?= htmlspecialchars($email) ?></strong></p>
      <p class="mb-0">Enter the code below to continue.</p>
    </div>

    <form method="post" action="<?= BASE_URL ?>/verify-2fa">
      <input type="hidden" name="_csrf" value="<?= $token ?>">
      <div class="mb-3">
        <label class="form-label">Verification Code</label>
        <input type="text" name="code" class="form-control" placeholder="Enter 6-digit code" maxlength="6" required autofocus>
        <div class="form-text">Check your email for the code (valid for 10 minutes)</div>
      </div>
      <button class="btn btn-primary w-100 mb-2">Verify & Login</button>
    </form>

    <form method="post" action="<?= BASE_URL ?>/resend-code">
      <input type="hidden" name="_csrf" value="<?= $token ?>">
      <button type="submit" class="btn btn-outline-secondary w-100">Resend Code</button>
    </form>

    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/login" class="text-muted">Back to Login</a>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';