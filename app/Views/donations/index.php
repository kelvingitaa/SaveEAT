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
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/donations"><i class="bi bi-heart"></i> Donations</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-heart"></i> Food Donations</h1>
                <a href="<?= BASE_URL ?>/donations/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> New Donation
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Expiring Items Alert -->
            <?php if (!empty($expiringItems)): ?>
                <div class="alert alert-warning">
                    <h5><i class="bi bi-exclamation-triangle"></i> Expiring Soon</h5>
                    <p class="mb-2">You have <?= count($expiringItems) ?> item(s) expiring in the next 2 days. Consider donating them to shelters.</p>
                    <div class="row">
                        <?php foreach (array_slice($expiringItems, 0, 3) as $item): ?>
                            <div class="col-md-4">
                                <small>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong> 
                                    (Expires: <?= date('M j', strtotime($item['expiry_date'])) ?>)
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Donations List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-list-check"></i> Your Donations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Food Item</th>
                                    <th>Shelter</th>
                                    <th>Quantity</th>
                                    <th>Donation Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($donation['food_name']) ?></td>
                                        <td><?= htmlspecialchars($donation['shelter_name']) ?></td>
                                        <td><?= $donation['quantity'] ?> portions</td>
                                        <td><?= date('M j, Y', strtotime($donation['donation_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $donation['status'] === 'completed' ? 'success' : 
                                                ($donation['status'] === 'scheduled' ? 'primary' : 'secondary')
                                            ?>">
                                                <?= ucfirst($donation['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($donation['status'] === 'scheduled'): ?>
                                                <form method="post" action="<?= BASE_URL ?>/donations/update-status" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-success btn-sm">Mark Completed</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($donations)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                            No donations yet. <a href="<?= BASE_URL ?>/donations/create">Create your first donation</a>.
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