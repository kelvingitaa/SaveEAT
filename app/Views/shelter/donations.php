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
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/shelter/donations"><i class="bi bi-basket"></i> Food Requests</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/history"><i class="bi bi-clock-history"></i> Donation History</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/shelter/settings"><i class="bi bi-gear"></i> Settings</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-basket"></i> Food Donation Requests</h2>
                <span class="badge bg-<?= $shelter['verified'] ? 'success' : 'warning' ?>">
                    <?= $shelter['verified'] ? 'Verified' : 'Pending Verification' ?>
                </span>
            </div>

            <?php if (!$shelter['verified']): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Your shelter needs to be verified before you can request donations.
                </div>
            <?php else: ?>

            <!-- Available Food Donations -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-basket"></i> Available Food Donations</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableDonations)): ?>
                        <div class="row">
                            <?php foreach ($availableDonations as $food): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between">
                                            <h6 class="card-title mb-0"><?= htmlspecialchars($food['name']) ?></h6>
                                            <span class="badge bg-<?= $food['hours_until_expiry'] < 24 ? 'danger' : 'warning' ?>">
                                                <?= floor($food['hours_until_expiry']/24) ?> days left
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text small"><?= htmlspecialchars($food['description']) ?></p>
                                            <ul class="list-unstyled small">
                                                <li><strong>Vendor:</strong> <?= htmlspecialchars($food['business_name']) ?></li>
                                                <li><strong>Location:</strong> <?= htmlspecialchars($food['vendor_location']) ?></li>
                                                <li><strong>Available:</strong> <?= (int)$food['stock'] ?> portions</li>
                                                <li><strong>Expires:</strong> <?= date('M j, Y', strtotime($food['expiry_date'])) ?></li>
                                            </ul>
                                        </div>
                                        <div class="card-footer">
                                            <form method="POST" action="<?= BASE_URL ?>/shelter/donations/request">
                                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                <input type="hidden" name="food_item_id" value="<?= $food['id'] ?>">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <input type="number" name="quantity" class="form-control form-control-sm" 
                                                               min="1" max="<?= $food['stock'] ?>" value="1" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                                            <i class="bi bi-cart-plus"></i> Request
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <textarea name="notes" class="form-control form-control-sm" 
                                                              placeholder="Additional notes (optional)" rows="2"></textarea>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="text-muted mt-2">No available food donations</h5>
                            <p class="text-muted">Check back later for new donations from vendors.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Your Donation Requests -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-list-check"></i> Your Donation Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($shelterDonations)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Food Item</th>
                                        <th>Vendor</th>
                                        <th>Quantity</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shelterDonations as $donation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($donation['food_name']) ?></td>
                                            <td><?= htmlspecialchars($donation['business_name']) ?></td>
                                            <td><?= (int)$donation['quantity'] ?></td>
                                            <td><?= date('M j, Y', strtotime($donation['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $donation['status'] === 'completed' ? 'success' : 
                                                    ($donation['status'] === 'scheduled' ? 'primary' : 
                                                    ($donation['status'] === 'pending' ? 'warning' : 'secondary'))
                                                ?>">
                                                    <?= ucfirst($donation['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No donation requests yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php endif; ?>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';