<?php
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

