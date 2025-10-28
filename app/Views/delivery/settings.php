<?php
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="<?= BASE_URL ?>/delivery/dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="<?= BASE_URL ?>/delivery/history">
                            <i class="bi bi-clock-history"></i> Delivery History
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link active fw-bold" href="<?= BASE_URL ?>/delivery/settings">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-gear"></i> Driver Settings</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="bi bi-person"></i> Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= BASE_URL ?>/delivery/update-profile">
                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($driver['name'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($driver['phone'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Vehicle Type</label>
                                    <select name="vehicle_type" class="form-select" required>
                                        <option value="bicycle" <?= ($driver['vehicle_type'] ?? '') === 'bicycle' ? 'selected' : '' ?>>Bicycle</option>
                                        <option value="motorcycle" <?= ($driver['vehicle_type'] ?? '') === 'motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
                                        <option value="car" <?= ($driver['vehicle_type'] ?? '') === 'car' ? 'selected' : '' ?>>Car</option>
                                        <option value="van" <?= ($driver['vehicle_type'] ?? '') === 'van' ? 'selected' : '' ?>>Van</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">License Plate</label>
                                    <input type="text" name="license_plate" class="form-control" value="<?= htmlspecialchars($driver['license_plate'] ?? '') ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0"><i class="bi bi-shield-lock"></i> Account Security</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= BASE_URL ?>/delivery/change-password">
                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="8">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';