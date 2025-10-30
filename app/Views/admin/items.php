<?php
<<<<<<< HEAD
ob_start();
?>
<style>
  .admin-items-container { display: flex; }
  .admin-sidepanel { min-width: 220px; max-width: 260px; background: #f8fafc; border-radius: 1rem; padding: 2rem 1rem; margin-right: 2rem; height: 100%; }
  .admin-items-main { flex: 1; }
  .item-table-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 2rem; margin-bottom: 2rem; }
  .item-table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
  .item-table-header h2 { margin: 0; font-size: 1.5rem; color: #2563eb; }
  .item-table-filters { display: flex; gap: 1rem; }
  .item-table-filters select, .item-table-filters input { min-width: 120px; }
</style>
<div class="admin-items-container">
  <nav class="admin-sidepanel">
    <h5 class="mb-4 text-primary"><i class="bi bi-person-badge"></i> Admin Menu</h5>
    <ul class="nav flex-column gap-2">
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin">Dashboard Overview</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users">User Management</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors">Vendor Management</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories">Category Management</a></li>
      <li class="nav-item"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin/items">Food Items</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders">Order Management</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/logs">Audit Logs</a></li>
    </ul>
  </nav>
  <main class="admin-items-main">
    <div class="item-table-section">
      <div class="item-table-header">
        <h2><i class="bi bi-basket"></i> Food Item Management</h2>
        <button class="btn btn-primary" onclick="alert('Add Item')"><i class="bi bi-plus"></i> Add Item</button>
      </div>
      <form class="item-table-filters mb-3" method="get" action="<?= BASE_URL ?>/admin/items">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <!-- Optionally populate categories here -->
        </select>
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="expired">Expired</option>
        </select>
        <input type="text" name="q" class="form-control" placeholder="Search name/vendor" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
      </form>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Category</th>
              <th>Vendor</th>
              <th>Price</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($items ?? []) as $i): ?>
              <tr>
                <td><?= (int)$i['id'] ?></td>
                <td><?= htmlspecialchars($i['name']) ?></td>
                <td><?= htmlspecialchars($i['category_id'] ?? '-') ?></td>
                <td><?= htmlspecialchars($i['vendor_id'] ?? '-') ?></td>
                <td><?= isset($i['price']) ? 'Ksh ' . number_format($i['price'],2) : '-' ?></td>
                <td><span class="badge bg-<?= $i['status'] === 'active' ? 'success' : ($i['status'] === 'inactive' ? 'warning' : 'danger') ?> text-capitalize"><?= htmlspecialchars($i['status']) ?></span></td>
                <td><?= isset($i['created_at']) ? htmlspecialchars($i['created_at']) : '-' ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-secondary" title="Edit" onclick="alert('Edit Item')"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="if(confirm('Delete item?'))alert('Delete Item')"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?><tr><td colspan="8" class="text-center text-muted">No food items found.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

=======
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
>>>>>>> fbe2f2352f51f03e7ea1f2afe40b2cc8d8bb19ff
