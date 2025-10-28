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
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="<?= BASE_URL ?>/delivery/dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link active fw-bold" href="<?= BASE_URL ?>/delivery/history">
                            <i class="bi bi-clock-history"></i> Delivery History
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="<?= BASE_URL ?>/delivery/settings">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-clock-history"></i> Delivery History</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-list-task"></i> Completed Deliveries</h5>
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
                                    <th>Completed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completedDeliveries as $delivery): ?>
                                    <tr>
                                        <td>#<?= $delivery['order_id'] ?></td>
                                        <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($delivery['delivery_address']) ?></td>
                                        <td>KSh <?= number_format($delivery['total_price'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($delivery['delivery_time'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($completedDeliveries)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No completed deliveries yet.
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