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