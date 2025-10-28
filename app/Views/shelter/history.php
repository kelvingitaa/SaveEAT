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
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/shelter/history"><i class="bi bi-clock-history"></i> Donation History</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/settings"><i class="bi bi-gear"></i> Settings</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-clock-history"></i> Donation History</h2>
                <div class="btn-toolbar">
                    <span class="badge bg-success fs-6">
                        <?= $stats['completed_donations'] ?? 0 ?> Completed Donations
                    </span>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['completed_donations'] ?? 0 ?></h3>
                            <p class="mb-0">Completed Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?= $stats['total_items_received'] ?? 0 ?></h3>
                            <p class="mb-0">Total Items Received</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3><?= $stats['pending_requests'] ?? 0 ?></h3>
                            <p class="mb-0">Pending Requests</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation History Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul"></i> All Donations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($donations)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Vendor</th>
                                        <th>Quantity</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($donation['food_name']) ?></strong>
                                                <?php if (!empty($donation['description'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($donation['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($donation['business_name']) ?></td>
                                            <td><?= (int)$donation['quantity'] ?></td>
                                            <td><?= date('M j, Y', strtotime($donation['donation_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $donation['status'] === 'completed' ? 'success' : 
                                                    ($donation['status'] === 'scheduled' ? 'primary' : 
                                                    ($donation['status'] === 'pending' ? 'warning' : 'secondary'))
                                                ?>">
                                                    <?= ucfirst($donation['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($donation['notes'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="text-muted mt-3">No donation history</h5>
                            <p class="text-muted">Your donation requests will appear here once you start requesting food.</p>
                            <a href="<?= BASE_URL ?>/shelter/donations" class="btn btn-primary">Request Food Donations</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';