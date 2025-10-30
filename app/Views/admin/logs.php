<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> Users</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/shelters"><i class="bi bi-house-heart"></i> Shelters</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/approvals"><i class="bi bi-shield-check"></i> Approvals</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/food"><i class="bi bi-basket"></i> Food Items</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/donations"><i class="bi bi-heart"></i> Donations</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin/logs"><i class="bi bi-clock-history"></i> Audit Logs</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-clock-history"></i> Audit Logs</h1>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Audit logs functionality will be implemented in a future update.
            </div>

            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-clock-history display-4 mb-3"></i>
                    <h4>Audit Logs Coming Soon</h4>
                    <p>This feature is currently under development.</p>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';