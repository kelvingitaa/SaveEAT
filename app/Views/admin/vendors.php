<?php
ob_start();
?>
<style>
  .vendor-table-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 2rem; margin-bottom: 2rem; }
  .vendor-table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
  .vendor-table-header h2 { margin: 0; font-size: 1.5rem; color: #2563eb; }
  .vendor-table-filters { display: flex; gap: 1rem; }
  .vendor-table-filters select, .vendor-table-filters input { min-width: 120px; }
</style>
<div class="vendor-table-section">
  <div class="vendor-table-header">
    <h2>Vendor Management</h2>
    <button class="btn btn-primary" onclick="alert('Add Vendor')">Add Vendor</button>
  </div>
  <form class="vendor-table-filters mb-3" method="get" action="<?= BASE_URL ?>/admin/vendors">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
    </select>
    <input type="text" name="q" class="form-control" placeholder="Search business/location" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button class="btn btn-outline-primary">Filter</button>
  </form>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Business Name</th>
          <th>Location</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($vendors ?? []) as $v): ?>
          <tr>
            <td><?= htmlspecialchars($v['business_name']) ?></td>
            <td><?= htmlspecialchars($v['location']) ?></td>
            <td><?= htmlspecialchars($v['contact_phone']) ?></td>
            <td><span class="badge bg-<?= $v['approved'] ? 'success' : 'warning' ?>"><?= $v['approved'] ? 'Active' : 'Pending' ?></span></td>
            <td>
              <button class="btn btn-sm btn-outline-success" onclick="alert('Approve Vendor')">Approve</button>
              <button class="btn btn-sm btn-outline-secondary" onclick="alert('Edit Vendor')">Edit</button>
              <button class="btn btn-sm btn-outline-warning" onclick="alert('Suspend Vendor')">Suspend</button>
              <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete vendor?'))alert('Delete Vendor')">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($vendors)): ?><tr><td colspan="5" class="text-center text-muted">No vendors found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
$token = CSRF::token();
ob_start();
?>
<h3>Vendors</h3>
<table class="table table-bordered table-striped">
  <thead><tr><th>ID</th><th>Business</th><th>User</th><th>Email</th><th>Approved</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach ($vendors as $v): ?>
    <tr>
      <td><?= (int)$v['id'] ?></td>
      <td><?= htmlspecialchars($v['business_name']) ?></td>
      <td><?= htmlspecialchars($v['user_name']) ?></td>
      <td><?= htmlspecialchars($v['email']) ?></td>
      <td><?= $v['approved'] ? 'Yes' : 'No' ?></td>
      <td>
        <?php if (!$v['approved']): ?>
          <form method="post" action="<?= BASE_URL ?>/admin/vendors/approve" class="d-inline">
            <input type="hidden" name="_csrf" value="<?= $token ?>">
            <input type="hidden" name="vendor_id" value="<?= (int)$v['id'] ?>">
            <button class="btn btn-sm btn-success">Approve</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
