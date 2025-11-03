<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <!-- Main Content Only - No Sidebar -->
        <main class="col-12 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-receipt"></i> Order Management</h1>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($order['customer_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($order['email']) ?></small>
                                </td>
                                <td>KSh <?= number_format($order['total_price'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'paid' ? 'primary' : 'warning')
                                    ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No orders found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';