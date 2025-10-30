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
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> Users</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin/shelters"><i class="bi bi-house-heart"></i> Shelters</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/verifications"><i class="bi bi-shield-check"></i> Verifications</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-house-heart"></i> Shelter Management</h1>
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

            <!-- Pending Shelters -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Pending Verification</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Shelter Name</th>
                                    <th>Contact Person</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Contact Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingShelters as $shelter): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($shelter['shelter_name']) ?></td>
                                        <td><?= htmlspecialchars($shelter['contact_person'] ?? $shelter['name']) ?></td>
                                        <td><?= htmlspecialchars($shelter['location']) ?></td>
                                        <td><?= (int)$shelter['capacity'] ?></td>
                                        <td><?= htmlspecialchars($shelter['contact_phone']) ?></td>
                                        <td>
                                            <form method="post" action="<?= BASE_URL ?>/admin/shelters/approve" class="d-inline">
                                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                <input type="hidden" name="shelter_id" value="<?= $shelter['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <a href="#" class="btn btn-outline-primary btn-sm">View Documents</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($pendingShelters)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No pending shelters for verification.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Shelters -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-check-circle"></i> Verified Shelters</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Shelter Name</th>
                                    <th>Contact Email</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Contact Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeShelters as $shelter): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($shelter['shelter_name']) ?></td>
                                        <td><?= htmlspecialchars($shelter['email']) ?></td>
                                        <td><?= htmlspecialchars($shelter['location']) ?></td>
                                        <td><?= (int)$shelter['capacity'] ?></td>
                                        <td><?= htmlspecialchars($shelter['contact_phone']) ?></td>
                                        <td>
                                            <span class="badge bg-success">Active</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($activeShelters)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No verified shelters yet.
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