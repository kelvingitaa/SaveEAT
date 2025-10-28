<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/dashboard"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/donations"><i class="bi bi-basket"></i> Food Requests</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/history"><i class="bi bi-clock-history"></i> Donation History</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/shelter/settings"><i class="bi bi-gear"></i> Settings</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <h2><i class="bi bi-gear"></i> Shelter Settings</h2>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Shelter Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/shelter/settings/update">
                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="shelter_name" class="form-label">Shelter Name *</label>
                                        <input type="text" class="form-control" id="shelter_name" name="shelter_name" 
                                               value="<?= htmlspecialchars($shelter['shelter_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_person" class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                               value="<?= htmlspecialchars($user['name']) ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="location" class="form-label">Location *</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?= htmlspecialchars($shelter['location']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_phone" class="form-label">Contact Phone *</label>
                                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                               value="<?= htmlspecialchars($shelter['contact_phone']) ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="capacity" class="form-label">Shelter Capacity</label>
                                        <input type="number" class="form-control" id="capacity" name="capacity" 
                                               value="<?= (int)$shelter['capacity'] ?>" min="1">
                                        <div class="form-text">Number of people your shelter can accommodate</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Verification Status</label>
                                        <div class="form-control bg-<?= $shelter['verified'] ? 'success' : 'warning' ?> text-white">
                                            <?= $shelter['verified'] ? 'Verified' : 'Pending Verification' ?>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Email:</strong><br>
                                <?= htmlspecialchars($user['email']) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Account Role:</strong><br>
                                <span class="badge bg-info">Shelter</span>
                            </div>
                            <div class="mb-3">
                                <strong>Registration Date:</strong><br>
                                <?= date('M j, Y', strtotime($shelter['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-outline-primary">
                                    <i class="bi bi-basket"></i> Request Food
                                </a>
                                <a href="<?= BASE_URL ?>/shelter/history" class="btn btn-outline-success">
                                    <i class="bi bi-clock-history"></i> View History
                                </a>
                                <a href="<?= BASE_URL ?>/shelter/dashboard" class="btn btn-outline-info">
                                    <i class="bi bi-house"></i> Dashboard
                                </a>
                            </div>
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