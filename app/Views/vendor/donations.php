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
      

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-heart"></i> Food Donations</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= BASE_URL ?>/vendor" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Donations Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Donations</h5>
                            <h2 class="card-text"><?= count($donations) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Completed</h5>
                            <h2 class="card-text">
                                <?= count(array_filter($donations, fn($d) => $d['status'] === 'completed')) ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="card-text">
                                <?= count(array_filter($donations, fn($d) => $d['status'] === 'pending')) ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Scheduled</h5>
                            <h2 class="card-text">
                                <?= count(array_filter($donations, fn($d) => $d['status'] === 'scheduled')) ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donations List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul"></i> Donation Requests (<?= count($donations) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($donations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Food Item</th>
                                        <th>Shelter</th>
                                        <th>Quantity</th>
                                        <th>Expiry Date</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?= (int)$donation['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($donation['food_name'] ?? 'Unknown Food') ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($donation['shelter_name'] ?? 'Unknown Shelter') ?></strong>
                                            <?php if (isset($donation['shelter_location'])): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($donation['shelter_location']) ?>
                                                    <?php if (isset($donation['shelter_phone'])): ?>
                                                        <br>Tel: <?= htmlspecialchars($donation['shelter_phone']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= (int)($donation['quantity'] ?? 0) ?></span>
                                        </td>
                                        <td>
                                            <?php if (isset($donation['expiry_date'])): 
                                                $expiryDate = new DateTime($donation['expiry_date']);
                                                $today = new DateTime();
                                                $daysUntilExpiry = $today->diff($expiryDate)->days;
                                                
                                                if ($expiryDate < $today): ?>
                                                    <span class="badge bg-danger">
                                                        <?= $expiryDate->format('M j, Y') ?>
                                                    </span>
                                                <?php elseif ($daysUntilExpiry <= 1): ?>
                                                    <span class="badge bg-warning">
                                                        <?= $expiryDate->format('M j, Y') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <?= $expiryDate->format('M j, Y') ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($donation['created_at'])): ?>
                                                <?= date('M j, Y', strtotime($donation['created_at'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                ($donation['status'] ?? 'pending') === 'completed' ? 'success' : 
                                                (($donation['status'] ?? 'pending') === 'scheduled' ? 'primary' : 
                                                (($donation['status'] ?? 'pending') === 'pending' ? 'warning' : 'secondary'))
                                            ?>">
                                                <?= ucfirst($donation['status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if (($donation['status'] ?? 'pending') === 'pending'): ?>
                                                    <form method="post" action="<?= BASE_URL ?>/vendor/donations/update-status" class="d-inline">
                                                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                        <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">
                                                        <input type="hidden" name="status" value="scheduled">
                                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                                            <i class="bi bi-check"></i> Accept
                                                        </button>
                                                    </form>
                                                    <form method="post" action="<?= BASE_URL ?>/vendor/donations/update-status" class="d-inline">
                                                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                        <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                                onclick="return confirm('Are you sure you want to decline this donation request?')">
                                                            <i class="bi bi-x"></i> Decline
                                                        </button>
                                                    </form>
                                                <?php elseif (($donation['status'] ?? 'pending') === 'scheduled'): ?>
                                                    <form method="post" action="<?= BASE_URL ?>/vendor/donations/update-status" class="d-inline">
                                                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                        <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-check-all"></i> Mark Complete
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">No actions</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox display-4 d-block mb-3"></i>
                            <h5>No donation requests yet</h5>
                            <p>When shelters request your food items for donation, they will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> About Food Donations</h5>
                </div>
                <div class="card-body">
                    <p>This section shows donation requests from shelters for your surplus food items.</p>
                    <ul>
                        <li><strong>Pending:</strong> New requests waiting for your response</li>
                        <li><strong>Scheduled:</strong> Requests you've accepted and scheduled for pickup</li>
                        <li><strong>Completed:</strong> Successfully delivered donations</li>
                        <li><strong>Cancelled:</strong> Requests that were declined or cancelled</li>
                    </ul>
                    <p class="mb-0"><small class="text-muted">Help reduce food waste by donating surplus food to shelters in need.</small></p>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';