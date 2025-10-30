
<?php
ob_start();
?>
<div class="vendor-table-section" style="background:#fff;border-radius:1rem;box-shadow:0 2px 8px rgba(30,64,175,0.06);padding:2rem;margin-bottom:2rem;">
  <div class="vendor-table-header d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold text-primary mb-0"><i class="bi bi-shop"></i> Vendor Management</h2>
    <button class="btn btn-primary" onclick="alert('Add Vendor')"><i class="bi bi-plus"></i> Add Vendor</button>
  </div>
  <form class="vendor-table-filters d-flex gap-2 mb-3" method="get" action="<?= BASE_URL ?>/admin/vendors">
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
    </select>
    <input type="text" name="q" class="form-control" placeholder="Search business/location" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
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
            <td><?= htmlspecialchars($v['location'] ?? '-') ?></td>
            <td><?= htmlspecialchars($v['contact'] ?? $v['contact_phone'] ?? '-') ?></td>
            <td><span class="badge bg-<?= ($v['status'] ?? ($v['approved'] ? 'active' : 'pending')) === 'active' ? 'success' : (($v['status'] ?? ($v['approved'] ? 'active' : 'pending')) === 'pending' ? 'warning' : 'danger') ?> text-capitalize">
              <?= htmlspecialchars($v['status'] ?? ($v['approved'] ? 'Active' : 'Pending')) ?></span></td>
            <td>
              <button class="btn btn-sm btn-outline-secondary" title="Edit" onclick="alert('Edit Vendor')"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="if(confirm('Delete vendor?'))alert('Delete Vendor')"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($vendors)): ?><tr><td colspan="5" class="text-center text-muted">No vendors found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
