<?php
use App\Core\Session;

$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Flash Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-receipt"></i> Order Details #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h2>
                <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
            </div>

            <div class="row">
                <!-- Order Summary -->
                <div class="col-md-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="bi bi-basket"></i> Order Items</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Discount</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['image_path']): ?>
                                                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['food_name']) ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                                <i class="bi bi-image text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= htmlspecialchars($item['food_name']) ?></strong>
                                                            <?php if ($item['description']): ?>
                                                                <br><small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>KSh <?= number_format($item['unit_price'], 2) ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td>
                                                    <?php if ($item['discount_percent'] > 0): ?>
                                                        <span class="badge bg-success">-<?= $item['discount_percent'] ?>%</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong>KSh <?= number_format($item['line_total'], 2) ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                                            <td><strong class="text-success">KSh <?= number_format($order['total_price'], 2) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Tracking -->
                    <?php if ($delivery): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0"><i class="bi bi-truck"></i> Delivery Tracking</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($deliveryStatus): ?>
                                    <div class="timeline">
                                        <?php foreach ($deliveryStatus['timeline'] as $step): ?>
                                            <div class="timeline-item <?= $step['completed'] ? 'completed' : ($step['active'] ? 'current' : 'future') ?>">
                                                <div class="timeline-icon">
                                                    <?= $step['icon'] ?>
                                                </div>
                                                <div class="timeline-content">
                                                    <h6><?= $step['label'] ?></h6>
                                                    <?php if ($step['active']): ?>
                                                        <div class="progress mt-2">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 75%">
                                                                In Progress
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if ($delivery['driver_name']): ?>
                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h6><i class="bi bi-person-badge"></i> Your Driver</h6>
                                            <p class="mb-1"><strong><?= htmlspecialchars($delivery['driver_name']) ?></strong></p>
                                            <p class="mb-1"><i class="bi bi-telephone"></i> <?= htmlspecialchars($delivery['driver_phone'] ?? 'Contact info available') ?></p>
                                            <p class="mb-0"><i class="bi bi-truck"></i> <?= htmlspecialchars($delivery['vehicle_type'] ?? 'Vehicle') ?> - <?= htmlspecialchars($delivery['license_plate'] ?? '') ?></p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">Delivery information is being processed...</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Order Information Sidebar -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Order Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Order Status:</strong><br>
                                <?php
                                $statusClass = [
                                    'pending' => 'warning',
                                    'paid' => 'info',
                                    'preparing' => 'primary',
                                    'ready' => 'success',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ][$order['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusClass ?> mt-1">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Order Date:</strong><br>
                                <span class="text-muted">
                                    <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Last Updated:</strong><br>
                                <span class="text-muted">
                                    <?= date('F j, Y \a\t g:i A', strtotime($order['updated_at'])) ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Order Total:</strong><br>
                                <span class="text-success fw-bold fs-5">
                                    KSh <?= number_format($order['total_price'], 2) ?>
                                </span>
                            </div>

                            <?php if ($delivery): ?>
                                <div class="mb-3">
                                    <strong>Delivery Status:</strong><br>
                                    <span class="badge bg-<?= 
                                        $delivery['status'] === 'delivered' ? 'success' : 
                                        ($delivery['status'] === 'in_transit' ? 'primary' : 'warning')
                                    ?> mt-1">
                                        <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                                    </span>
                                </div>

                                <?php if ($delivery['delivery_address']): ?>
                                    <div class="mb-3">
                                        <strong>Delivery Address:</strong><br>
                                        <span class="text-muted"><?= htmlspecialchars($delivery['delivery_address']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($delivery['pickup_time']): ?>
                                    <div class="mb-3">
                                        <strong>Pickup Time:</strong><br>
                                        <span class="text-muted">
                                            <?= date('M j, g:i A', strtotime($delivery['pickup_time'])) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($delivery['delivery_time']): ?>
                                    <div class="mb-3">
                                        <strong>Delivery Time:</strong><br>
                                        <span class="text-muted">
                                            <?= date('M j, g:i A', strtotime($delivery['delivery_time'])) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-arrow-left"></i> Back to Orders
                                </a>
                                <?php if ($delivery && ($order['status'] === 'preparing' || $order['status'] === 'ready')): ?>
                                    <a href="<?= BASE_URL ?>/delivery/track/<?= $order['id'] ?>" class="btn btn-success w-100 mt-2">
                                        <i class="bi bi-truck"></i> Track Delivery
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-left: 30px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item.completed::before {
    background: #28a745;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: -30px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    background: white;
    border: 2px solid #dee2e6;
    z-index: 2;
}

.timeline-item.completed .timeline-icon {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.timeline-item.current .timeline-icon {
    background: #007bff;
    color: white;
    border-color: #007bff;
    animation: pulse 2s infinite;
}

.timeline-content {
    padding: 10px 0;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';