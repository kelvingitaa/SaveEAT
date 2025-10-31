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
                <h2><i class="bi bi-receipt"></i> My Orders</h2>
                <a href="<?= BASE_URL ?>/consumer" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Continue Shopping
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-cart-x display-1 text-muted"></i>
                        <h3 class="mt-3 text-muted">No Orders Yet</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="<?= BASE_URL ?>/consumer" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag"></i> Start Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
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
                                                <small class="text-muted">
                                                    <?php
                                                    // Get order items for this order
                                                    $orderItems = (new \App\Models\Order())->getOrderItems($order['id']);
                                                    $itemCount = count($orderItems);
                                                    $firstItem = $itemCount > 0 ? $orderItems[0]['food_name'] : 'No items';
                                                    
                                                    if ($itemCount > 0) {
                                                        echo '<i class="bi bi-basket"></i> ' . htmlspecialchars($firstItem);
                                                        if ($itemCount > 1) {
                                                            echo ' +' . ($itemCount - 1) . ' more';
                                                        }
                                                    } else {
                                                        echo '<i class="bi bi-basket"></i> No items';
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong class="text-success">KSh <?= number_format($order['total_price'] ?? 0, 0) ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusIcon = 'bi-clock';
                                                
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $statusClass = 'warning';
                                                        $statusIcon = 'bi-clock';
                                                        break;
                                                    case 'paid':
                                                        $statusClass = 'info';
                                                        $statusIcon = 'bi-credit-card';
                                                        break;
                                                    case 'preparing':
                                                        $statusClass = 'primary';
                                                        $statusIcon = 'bi-egg-fried';
                                                        break;
                                                    case 'ready':
                                                        $statusClass = 'success';
                                                        $statusIcon = 'bi-check-circle';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'success';
                                                        $statusIcon = 'bi-check-lg';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'danger';
                                                        $statusIcon = 'bi-x-circle';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <i class="bi <?= $statusIcon ?>"></i>
                                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php
                                                    $orderDate = new DateTime($order['created_at']);
                                                    echo $orderDate->format('M j, Y \a\t g:i A');
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                               <a href="<?= BASE_URL ?>/consumer/order-details?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
    <i class="bi bi-eye"></i> Details
</a>
                                                    <?php if ($order['status'] === 'preparing' || $order['status'] === 'ready'): ?>
                                                        <a href="<?= BASE_URL ?>/delivery/track/<?= $order['id'] ?>" class="btn btn-outline-success">
                                                            <i class="bi bi-truck"></i> Track
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Progress Legend -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-info-circle"></i> Order Status Guide</h5>
                        <div class="row text-center">
                            <div class="col-md-2">
                                <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                                <small class="d-block text-muted">Payment processing</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-info"><i class="bi bi-credit-card"></i> Paid</span>
                                <small class="d-block text-muted">Payment confirmed</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-primary"><i class="bi bi-egg-fried"></i> Preparing</span>
                                <small class="d-block text-muted">Food being prepared</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Ready</span>
                                <small class="d-block text-muted">Ready for pickup/delivery</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Completed</span>
                                <small class="d-block text-muted">Order delivered</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Cancelled</span>
                                <small class="d-block text-muted">Order cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.75em;
}
.table th {
    border-top: none;
    font-weight: 600;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';