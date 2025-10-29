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
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/vendor"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/vendor/items"><i class="bi bi-basket"></i> Food Items</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/vendor/orders"><i class="bi bi-receipt"></i> Orders</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/vendor/donations"><i class="bi bi-heart"></i> Donations</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-receipt"></i> Customer Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= BASE_URL ?>/vendor" class="btn btn-outline-secondary">
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

            <!-- Orders List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul"></i> Recent Orders (<?= count($orders) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Order Status</th>
                                    <th>Delivery Status</th>
                                    <th>Driver</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($order['customer_phone']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <?= htmlspecialchars($order['items']) ?>
                                            <?php if ($order['item_count'] > 1): ?>
                                                <br><span class="badge bg-info">+<?= $order['item_count'] - 1 ?> more</span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong>KSh <?= number_format($order['total_price'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'paid' => 'info',
                                            'preparing' => 'primary', 
                                            'ready' => 'success',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ][$order['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $deliveryStatusClass = [
                                            'pending_assignment' => 'warning',
                                            'assigned' => 'info',
                                            'vendor_confirmed' => 'primary',
                                            'picked_up' => 'primary',
                                            'in_transit' => 'success',
                                            'delivered' => 'success',
                                            'completed' => 'success'
                                        ][$order['delivery_status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $deliveryStatusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $order['delivery_status'] ?? 'Not assigned')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($order['driver_name']): ?>
                                            <small>
                                                <i class="bi bi-person"></i> <?= htmlspecialchars($order['driver_name']) ?>
                                                <br>
                                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($order['driver_phone'] ?? 'N/A') ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Waiting for driver</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, g:i A', strtotime($order['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($order['status'] === 'paid' && $order['delivery_status'] === 'assigned'): ?>
                                                <form method="post" action="<?= BASE_URL ?>/vendor/confirm-order" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-check-lg"></i> Confirm
                                                    </button>
                                                </form>
                                            <?php elseif ($order['status'] === 'preparing'): ?>
                                                <form method="post" action="<?= BASE_URL ?>/vendor/mark-order-ready" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-check2-all"></i> Ready
                                                    </button>
                                                </form>
                                            <?php elseif ($order['status'] === 'ready'): ?>
                                                <span class="badge bg-success">Ready for Pickup</span>
                                            <?php else: ?>
                                                <span class="text-muted">No actions</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No orders yet. Orders will appear here when customers purchase your items.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Status Guide -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Order Status Guide</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Order Status</h6>
                            <span class="badge bg-info">Paid</span> - Payment confirmed<br>
                            <span class="badge bg-primary">Preparing</span> - Food being prepared<br>
                            <span class="badge bg-success">Ready</span> - Ready for pickup<br>
                            <span class="badge bg-success">Completed</span> - Order fulfilled
                        </div>
                        <div class="col-md-4">
                            <h6>Delivery Status</h6>
                            <span class="badge bg-warning">Pending Assignment</span> - Waiting for driver<br>
                            <span class="badge bg-info">Assigned</span> - Driver assigned<br>
                            <span class="badge bg-primary">Vendor Confirmed</span> - You confirmed order<br>
                            <span class="badge bg-primary">Picked Up</span> - Driver collected order<br>
                            <span class="badge bg-success">In Transit</span> - On the way to customer<br>
                            <span class="badge bg-success">Delivered</span> - Order delivered
                        </div>
                        <div class="col-md-4">
                            <h6>Your Actions</h6>
                            • <strong>Confirm</strong> - Verify order when driver is assigned<br>
                            • <strong>Ready</strong> - Mark order ready for pickup<br>
                            • Driver will handle pickup confirmation
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