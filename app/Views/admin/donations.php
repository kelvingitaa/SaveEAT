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
                <h1 class="h2"><i class="bi bi-heart"></i> Donation Management</h1>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vendor</th>
                            <th>Shelter</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td>#<?= $donation['id'] ?></td>
                                <td><?= htmlspecialchars($donation['business_name'] ?? 'Unknown Vendor') ?></td>
                                <td><?= htmlspecialchars($donation['shelter_name'] ?? 'Unknown Shelter') ?></td>
                                <td><?= (int)$donation['quantity'] ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $donation['status'] === 'completed' ? 'success' : 
                                        ($donation['status'] === 'scheduled' ? 'primary' : 'warning')
                                    ?>">
                                        <?= ucfirst($donation['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($donation['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($donations)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No donations found.
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