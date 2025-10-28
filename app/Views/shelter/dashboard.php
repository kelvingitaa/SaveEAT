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
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/shelter/dashboard"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/donations"><i class="bi bi-basket"></i> Food Requests</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/history"><i class="bi bi-clock-history"></i> Donation History</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/settings"><i class="bi bi-gear"></i> Settings</a></li>
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
                                        <h3 class="text-primary"><?= $stats['pending_requests'] ?? 0 ?></h3>
                                        <small class="text-muted">Pending Requests</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h3 class="text-success"><?= $stats['completed_donations'] ?? 0 ?></h3>
                                        <small class="text-muted">Completed Donations</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col-12">
                                    <div class="border rounded p-2 bg-info text-white">
                                        <h4 class="mb-0"><?= $stats['total_items_received'] ?? 0 ?></h4>
                                        <small>Total Items Received</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-outline-primary w-100 py-3">
                                        <i class="bi bi-basket display-6 d-block mb-2"></i>
                                        Request Food
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/shelter/history" class="btn btn-outline-success w-100 py-3">
                                        <i class="bi bi-clock-history display-6 d-block mb-2"></i>
                                        View History
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/shelter/settings" class="btn btn-outline-info w-100 py-3">
                                        <i class="bi bi-gear display-6 d-block mb-2"></i>
                                        Settings
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-outline-warning w-100 py-3">
                                        <i class="bi bi-plus-circle display-6 d-block mb-2"></i>
                                        New Request
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Food Items -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-basket"></i> Available Food Donations</h5>
                    <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableDonations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Vendor</th>
                                        <th>Quantity</th>
                                        <th>Expires</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($availableDonations, 0, 5) as $food): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($food['name']) ?></strong>
                                                <?php if (!empty($food['description'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($food['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($food['business_name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $food['stock'] > 10 ? 'success' : ($food['stock'] > 5 ? 'warning' : 'danger') ?>">
                                                    <?= (int)$food['stock'] ?> portions
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('M j, Y', strtotime($food['expiry_date'])) ?>
                                                <br>
                                                <small class="text-<?= $food['hours_until_expiry'] < 24 ? 'danger' : ($food['hours_until_expiry'] < 72 ? 'warning' : 'success') ?>">
                                                    (<?= floor($food['hours_until_expiry']/24) ?> days left)
                                                </small>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-sm btn-success">
                                                    <i class="bi bi-cart-plus"></i> Request
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($availableDonations) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-outline-primary">
                                    View All <?= count($availableDonations) ?> Available Donations
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No available food donations at the moment.
                            <div class="mt-3">
                                <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-primary">Check for New Donations</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$shelter['verified']): ?>
                <div class="alert alert-warning mt-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-triangle display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="alert-heading">Verification Required</h5>
                            <p class="mb-2">Your shelter is pending verification. Please ensure you have submitted all required documents.</p>
                            <small>You will be able to request food donations once your shelter is verified by the administrator.</small>
                            <div class="mt-2">
                                <a href="<?= BASE_URL ?>/shelter/settings" class="btn btn-sm btn-outline-warning">Update Shelter Information</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success mt-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle display-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="alert-heading">Shelter Verified</h5>
                            <p class="mb-0">Your shelter is verified and ready to receive food donations. Start requesting food from available vendors!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';