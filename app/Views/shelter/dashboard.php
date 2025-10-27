<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidepanel -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="#"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-basket"></i> Food Requests</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-clock-history"></i> Donation History</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-house-heart"></i> Shelter Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-<?= $shelter['verified'] ? 'success' : 'warning' ?> fs-6">
                        <?= $shelter['verified'] ? 'Verified' : 'Pending Verification' ?>
                    </span>
                </div>
            </div>

            <!-- Shelter Info Card -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Shelter Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Shelter Name:</th>
                                    <td><?= htmlspecialchars($shelter['shelter_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td><?= htmlspecialchars($shelter['location']) ?></td>
                                </tr>
                                <tr>
                                    <th>Contact Phone:</th>
                                    <td><?= htmlspecialchars($shelter['contact_phone']) ?></td>
                                </tr>
                                <tr>
                                    <th>Capacity:</th>
                                    <td><?= (int)$shelter['capacity'] ?> people</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-<?= $shelter['status'] === 'active' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($shelter['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-speedometer2"></i> Quick Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h3 class="text-primary">0</h3>
                                        <small class="text-muted">Pending Requests</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h3 class="text-success">0</h3>
                                        <small class="text-muted">Completed Donations</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Food Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-basket"></i> Available Food Donations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Food Item</th>
                                    <th>Vendor</th>
                                    <th>Quantity</th>
                                    <th>Expires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No available food donations at the moment.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!$shelter['verified']): ?>
                <div class="alert alert-warning mt-4">
                    <h5><i class="bi bi-exclamation-triangle"></i> Verification Required</h5>
                    <p class="mb-2">Your shelter is pending verification. Please ensure you have submitted all required documents.</p>
                    <small>You will be able to request food donations once your shelter is verified.</small>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';