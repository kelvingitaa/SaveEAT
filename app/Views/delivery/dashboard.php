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
        <!-- Sidepanel -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="#"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-clock-history"></i> Delivery History</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-speedometer2"></i> Delivery Dashboard</h1>
                <div class="btn-group">
                    <form method="post" action="<?= BASE_URL ?>/delivery/update-status" class="d-inline">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                        <input type="hidden" name="status" value="<?= $driver['status'] === 'available' ? 'offline' : 'available' ?>">
                        <button type="submit" class="btn btn-<?= $driver['status'] === 'available' ? 'warning' : 'success' ?>">
                            <i class="bi bi-<?= $driver['status'] === 'available' ? 'pause' : 'play' ?>-circle"></i>
                            Go <?= $driver['status'] === 'available' ? 'Offline' : 'Online' ?>
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Status Card -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-<?= $driver['status'] === 'available' ? 'success' : 'secondary' ?>">
                        <div class="card-body text-center">
                            <i class="bi bi-<?= $driver['status'] === 'available' ? 'check-circle' : 'pause-circle' ?> display-4 text-<?= $driver['status'] === 'available' ? 'success' : 'secondary' ?>"></i>
                            <h5 class="card-title mt-2"><?= ucfirst($driver['status']) ?></h5>
                            <p class="card-text">Current Status</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-bicycle display-4 text-primary"></i>
                            <h5 class="card-title mt-2"><?= count($assignedDeliveries) ?></h5>
                            <p class="card-text">Active Deliveries</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-truck display-4 text-info"></i>
                            <h5 class="card-title mt-2"><?= $driver['vehicle_type'] ?? 'N/A' ?></h5>
                            <p class="card-text">Vehicle Type</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Deliveries -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-list-task"></i> Your Deliveries</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignedDeliveries as $delivery): ?>
                                    <tr>
                                        <td>#<?= $delivery['order_id'] ?></td>
                                        <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($delivery['delivery_address']) ?></td>
                                        <td>KSh <?= number_format($delivery['total_price'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $delivery['status'] === 'delivered' ? 'success' : 
                                                ($delivery['status'] === 'in_transit' ? 'primary' : 'warning')
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($delivery['status'] === 'assigned'): ?>
                                                <form method="post" action="<?= BASE_URL ?>/delivery/update-delivery-status" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="delivery_id" value="<?= $delivery['id'] ?>">
                                                    <input type="hidden" name="status" value="picked_up">
                                                    <button type="submit" class="btn btn-success btn-sm">Mark as Picked Up</button>
                                                </form>
                                            <?php elseif ($delivery['status'] === 'picked_up'): ?>
                                                <form method="post" action="<?= BASE_URL ?>/delivery/update-delivery-status" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="delivery_id" value="<?= $delivery['id'] ?>">
                                                    <input type="hidden" name="status" value="in_transit">
                                                    <button type="submit" class="btn btn-primary btn-sm">Start Delivery</button>
                                                </form>
                                            <?php elseif ($delivery['status'] === 'in_transit'): ?>
                                                <form method="post" action="<?= BASE_URL ?>/delivery/update-delivery-status" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="delivery_id" value="<?= $delivery['id'] ?>">
                                                    <input type="hidden" name="status" value="delivered">
                                                    <button type="submit" class="btn btn-success btn-sm">Mark as Delivered</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($assignedDeliveries)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No assigned deliveries.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';