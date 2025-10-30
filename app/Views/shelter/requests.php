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
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-basket"></i> Available Food Donations</h2>
                <span class="badge bg-success">Verified Shelter</span>
            </div>

            <?php if (!empty($availableDonations)): ?>
                <div class="row">
                    <?php foreach ($availableDonations as $food): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($food['name']) ?></h5>
                                    <span class="badge bg-<?= $food['hours_until_expiry'] < 24 ? 'danger' : 'warning' ?>">
                                        Expires in <?= floor($food['hours_until_expiry']/24) ?> days
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?= htmlspecialchars($food['description']) ?></p>
                                    <ul class="list-unstyled">
                                        <li><strong>Vendor:</strong> <?= htmlspecialchars($food['business_name']) ?></li>
                                        <li><strong>Location:</strong> <?= htmlspecialchars($food['vendor_location']) ?></li>
                                        <li><strong>Available Stock:</strong> <?= (int)$food['stock'] ?> portions</li>
                                        <li><strong>Storage:</strong> <?= htmlspecialchars($food['storage_instructions'] ?? 'Keep refrigerated') ?></li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <form method="POST" action="<?= BASE_URL ?>/shelter/donations/request" class="d-flex gap-2">
                                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                                        <input type="hidden" name="food_item_id" value="<?= $food['id'] ?>">
                                        <input type="number" name="quantity" class="form-control" min="1" max="<?= $food['stock'] ?>" 
                                               placeholder="Qty" required style="width: 100px;">
                                        <button type="submit" class="btn btn-success flex-fill">
                                            <i class="bi bi-cart-plus"></i> Request Donation
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No available food donations</h4>
                    <p class="text-muted">Check back later for new donations from vendors.</p>
                </div>
            <?php endif; ?>

            <!-- Donation Requests History -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Your Donation Requests</h5>
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
                                        <th>Request Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($donation['food_name']) ?></td>
                                            <td><?= htmlspecialchars($donation['business_name']) ?></td>
                                            <td><?= (int)$donation['quantity'] ?></td>
                                            <td><?= date('M j, Y', strtotime($donation['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $donation['status'] === 'completed' ? 'success' : 
                                                    ($donation['status'] === 'scheduled' ? 'primary' : 'warning')
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
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';