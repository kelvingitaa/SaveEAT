<?php
ob_start();
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
?>
<style>
  .dashboard-header { background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%); color: #fff; padding: 2rem 1rem; border-radius: 1rem; margin-bottom: 2rem; }
  .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
  .dashboard-card { background: #f8fafc; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.08); padding: 1.5rem; display: flex; align-items: center; gap: 1rem; }
  .dashboard-card .icon { font-size: 2rem; width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 0.75rem; }
  .dashboard-card .stat-title { font-weight: 600; color: #2563eb; margin-bottom: 0.25rem; }
  .dashboard-card .stat-value { font-size: 1.5rem; font-weight: bold; }
  .dashboard-card .stat-change.positive { color: #22c55e; }
  .dashboard-card .stat-change.negative { color: #f97316; }
  .dashboard-main { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
  @media (max-width: 900px) { .dashboard-main { grid-template-columns: 1fr; } }
  .dashboard-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 1.5rem; margin-bottom: 2rem; }
  .dashboard-sidebar { background: #f1f5f9; border-radius: 1rem; padding: 1.5rem; }
  .pending-approvals { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 1.5rem; }
  .approval-item { display: flex; justify-content: between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0; }
  .approval-item:last-child { border-bottom: none; }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <aside class="col-lg-3 col-md-4 mb-4">
            <nav class="bg-white rounded shadow-sm p-3 sticky-top" style="top:80px;">
                <h5 class="mb-3 text-primary">Admin Menu</h5>
                <ul class="nav flex-column gap-2">
                    <li class="nav-item"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2"></i> Dashboard Overview</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> User Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendor Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/shelters"><i class="bi bi-house-heart"></i> Shelter Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/approvals"><i class="bi bi-shield-check"></i> Registration Approvals</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories"><i class="bi bi-tags"></i> Category Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/food"><i class="bi bi-basket"></i> Food Item Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders"><i class="bi bi-receipt"></i> Order Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/donations"><i class="bi bi-heart"></i> Donation Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/logs"><i class="bi bi-clock-history"></i> Audit Logs</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="col-lg-9 col-md-8">
            <!-- Flash Messages -->
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

            <!-- Stats Cards -->
            <div class="dashboard-stats">
                <div class="dashboard-card">
                    <span class="icon" style="background:#2563eb1a;color:#2563eb;"><i class="bi bi-people"></i></span>
                    <div>
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value"><?= $userCount ?? '0' ?></div>
                        <div class="stat-change positive">+<?= round(($userCount ?? 0) * 0.12) ?> this week</div>
                    </div>
                </div>
                <div class="dashboard-card">
                    <span class="icon" style="background:#22c55e1a;color:#22c55e;"><i class="bi bi-currency-dollar"></i></span>
                    <div>
                        <div class="stat-title">Revenue</div>
                        <div class="stat-value">KSh <?= number_format($revenue ?? 0, 2) ?></div>
                        <div class="stat-change positive">+8.2%</div>
                    </div>
                </div>
                <div class="dashboard-card">
                    <span class="icon" style="background:#a21caf1a;color:#a21caf;"><i class="bi bi-shop"></i></span>
                    <div>
                        <div class="stat-title">Active Vendors</div>
                        <div class="stat-value"><?= $vendorCount ?? '0' ?></div>
                        <div class="stat-change positive">+<?= count($pendingVendors ?? []) ?> pending</div>
                    </div>
                </div>
                <div class="dashboard-card">
                    <span class="icon" style="background:#f973161a;color:#f97316;"><i class="bi bi-cart"></i></span>
                    <div>
                        <div class="stat-title">Total Orders</div>
                        <div class="stat-value"><?= $orderCount ?? '0' ?></div>
                        <div class="stat-change positive">+15%</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Pending Approvals -->
                    <div class="pending-approvals mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="bi bi-shield-check"></i> Pending Approvals</h5>
                            <a href="<?= BASE_URL ?>/admin/approvals" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        
                        <!-- Pending Vendors -->
                        <?php if (!empty($pendingVendors)): ?>
                            <div class="mb-3">
                                <h6 class="text-warning"><i class="bi bi-shop"></i> Vendors (<?= count($pendingVendors) ?>)</h6>
                                <?php foreach (array_slice($pendingVendors, 0, 3) as $vendor): ?>
                                    <div class="approval-item">
                                        <div>
                                            <strong><?= htmlspecialchars($vendor['business_name'] ?? 'Unknown Business') ?></strong>
                                            <small class="text-muted d-block"><?= htmlspecialchars($vendor['location'] ?? 'No location specified') ?></small>
                                        </div>
                                        <form method="post" action="<?= BASE_URL ?>/admin/approve-vendor" class="d-inline">
                                            <input type="hidden" name="_csrf" value="<?= $token ?>">
                                            <input type="hidden" name="vendor_id" value="<?= $vendor['id'] ?? '' ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Pending Shelters -->
                        <?php if (!empty($pendingShelters)): ?>
                            <div class="mb-3">
                                <h6 class="text-primary"><i class="bi bi-house-heart"></i> Shelters (<?= count($pendingShelters) ?>)</h6>
                                <?php foreach (array_slice($pendingShelters, 0, 3) as $shelter): ?>
                                    <div class="approval-item">
                                        <div>
                                            <strong><?= htmlspecialchars($shelter['shelter_name'] ?? 'Unknown Shelter') ?></strong>
                                            <small class="text-muted d-block">Capacity: <?= (int)($shelter['capacity'] ?? 0) ?></small>
                                        </div>
                                        <form method="post" action="<?= BASE_URL ?>/admin/approve-shelter" class="d-inline">
                                            <input type="hidden" name="_csrf" value="<?= $token ?>">
                                            <input type="hidden" name="shelter_id" value="<?= $shelter['id'] ?? '' ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($pendingVendors) && empty($pendingShelters)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-check-circle display-4"></i>
                                <p class="mt-2">No pending approvals</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Users -->
                    <div class="dashboard-section">
                        <h5 class="mb-3">Recent Users</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($users ?? []) as $u): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($u['name'] ?? 'Unknown') ?></td>
                                            <td><?= htmlspecialchars($u['email'] ?? 'No email') ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    ($u['role'] ?? 'consumer') === 'admin' ? 'danger' : 
                                                    (($u['role'] ?? 'consumer') === 'vendor' ? 'warning' : 'primary')
                                                ?>">
                                                    <?= ucfirst($u['role'] ?? 'consumer') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    ($u['status'] ?? 'active') === 'active' ? 'success' : 
                                                    (($u['status'] ?? 'active') === 'pending' ? 'warning' : 'danger')
                                                ?>">
                                                    <?= ucfirst($u['status'] ?? 'active') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Quick Stats -->
                    <div class="dashboard-sidebar mb-4">
                        <h6 class="mb-3">Quick Stats</h6>
                        <div class="mb-3">
                            <small class="text-muted">Consumers</small>
                            <div class="fw-bold"><?= $consumerCount ?? '0' ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Active Food Items</small>
                            <div class="fw-bold"><?= $foodActive ?? '0' ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Expiring Soon</small>
                            <div class="fw-bold text-warning"><?= count($expiringItems ?? []) ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Suspended Vendors</small>
                            <div class="fw-bold text-danger"><?= count($suspendedVendors ?? []) ?></div>
                        </div>
                    </div>

                    <!-- Expiring Items -->
                    <?php if (!empty($expiringItems)): ?>
                        <div class="dashboard-sidebar mb-4">
                            <h6 class="mb-3 text-warning">Expiring Soon</h6>
                            <?php foreach (array_slice($expiringItems, 0, 5) as $item): ?>
                                <div class="mb-2">
                                    <div class="fw-bold small"><?= htmlspecialchars($item['name'] ?? 'Unknown Item') ?></div>
                                    <small class="text-muted">Expires: <?= date('M j', strtotime($item['expiry_date'] ?? 'now')) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Top Items -->
                    <?php if (!empty($topItems)): ?>
                        <div class="dashboard-sidebar">
                            <h6 class="mb-3">Top Selling Items</h6>
                            <?php foreach ($topItems as $item): ?>
                                <div class="mb-2">
                                    <div class="fw-bold small"><?= htmlspecialchars($item['name'] ?? 'Unknown Item') ?></div>
                                    <small class="text-muted">Sold: <?= (int)($item['sold'] ?? 0) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>