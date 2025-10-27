<?php
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-house-heart"></i> Shelter Registration</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/shelter/register">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        
                        <h5 class="mb-3">Contact Person Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h5 class="mb-3">Shelter Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Shelter Name *</label>
                                <input type="text" name="shelter_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone *</label>
                                <input type="text" name="contact_phone" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Location/Address *</label>
                                <input type="text" name="location" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Capacity (People)</label>
                                <input type="number" name="capacity" class="form-control" min="1">
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i> 
                            Your registration will be reviewed and verified by our team. 
                            You'll need to provide verification documents to complete the process.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Register Shelter</button>
                            <a href="<?= BASE_URL ?>/login" class="btn btn-outline-secondary">Already have an account? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';