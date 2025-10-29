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
    <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4" style="min-height:100vh;">
      <div class="sidebar-sticky">
        <ul class="nav flex-column">
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin"><i class="bi bi-house"></i> Dashboard</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> Users</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
          <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin/food"><i class="bi bi-basket"></i> Food Items</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/donations"><i class="bi bi-heart"></i> Donations</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        </ul>
      </div>
    </nav>
    <!-- Main Content -->
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-basket"></i> Food Item Management</h1>
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

      <!-- Food Item Table -->
      <div class="card mb-4">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Vendor</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Expiry Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($foodItems as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($item['category_name'] ?? 'No Category') ?></td>
                    <td><?= htmlspecialchars($item['business_name'] ?? 'Unknown Vendor') ?></td>
                    <td>KSh <?= number_format($item['price'] ?? 0, 2) ?></td>
                    <td><?= (int)($item['stock'] ?? 0) ?></td>
                    <td>
                      <span class="badge bg-<?= 
                        ($item['status'] ?? 'active') === 'active' ? 'success' : 
                        (($item['status'] ?? 'active') === 'expired' ? 'danger' : 'warning')
                      ?>">
                        <?= ucfirst($item['status'] ?? 'active') ?>
                      </span>
                    </td>
                    <td><?= date('M j, Y', strtotime($item['expiry_date'] ?? 'now')) ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($foodItems)): ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                      No food items found.
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
?>