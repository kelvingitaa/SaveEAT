

<?php
ob_start();
?>
<div class="user-table-section" style="background:#fff;border-radius:1rem;box-shadow:0 2px 8px rgba(30,64,175,0.06);padding:2rem;margin-bottom:2rem;">
  <div class="user-table-header d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold text-primary mb-0"><i class="bi bi-people"></i> User Management</h2>
    <button class="btn btn-primary" onclick="alert('Add User')"><i class="bi bi-plus"></i> Add User</button>
  </div>
  <form class="user-table-filters d-flex gap-2 mb-3" method="get" action="<?= BASE_URL ?>/admin/users">
    <select name="role" class="form-select">
      <option value="">All Roles</option>
      <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
      <option value="vendor" <?= ($_GET['role'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor</option>
      <option value="consumer" <?= ($_GET['role'] ?? '') === 'consumer' ? 'selected' : '' ?>>Consumer</option>
    </select>
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
    </select>
    <input type="text" name="q" class="form-control" placeholder="Search name/email" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
  </form>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($users ?? []) as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge bg-primary text-white text-capitalize"><?= htmlspecialchars($u['role']) ?></span></td>
            <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : ($u['status'] === 'pending' ? 'warning' : 'danger') ?> text-capitalize"><?= htmlspecialchars($u['status']) ?></span></td>
            <td><?= isset($u['created_at']) ? htmlspecialchars($u['created_at']) : '-' ?></td>
            <td>
              <button class="btn btn-sm btn-outline-secondary" title="Edit" onclick="alert('Edit User')"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-warning" title="Suspend" onclick="alert('Suspend User')"><i class="bi bi-slash-circle"></i></button>
              <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="if(confirm('Delete user?'))alert('Delete User')"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?><tr><td colspan="6" class="text-center text-muted">No users found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
