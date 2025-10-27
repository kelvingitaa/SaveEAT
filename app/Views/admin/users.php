<?php
ob_start();
?>
<style>
  .user-table-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 2rem; margin-bottom: 2rem; }
  .user-table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
  .user-table-header h2 { margin: 0; font-size: 1.5rem; color: #2563eb; }
  .user-table-filters { display: flex; gap: 1rem; }
  .user-table-filters select, .user-table-filters input { min-width: 120px; }
</style>
<div class="user-table-section">
  <div class="user-table-header">
    <h2>User Management</h2>
    <button class="btn btn-primary" onclick="alert('Add User')">Add User</button>
  </div>
  <form class="user-table-filters mb-3" method="get" action="<?= BASE_URL ?>/admin/users">
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
    <button class="btn btn-outline-primary">Filter</button>
  </form>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($users ?? []) as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge bg-primary text-white"><?= htmlspecialchars($u['role']) ?></span></td>
            <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : ($u['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($u['status']) ?></span></td>
            <td>
              <button class="btn btn-sm btn-outline-secondary" onclick="alert('Edit User')">Edit</button>
              <button class="btn btn-sm btn-outline-warning" onclick="alert('Suspend User')">Suspend</button>
              <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete user?'))alert('Delete User')">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?><tr><td colspan="5" class="text-center text-muted">No users found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
ob_start();
?>
<h3>Users</h3>
<table class="table table-bordered table-striped">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td><?= htmlspecialchars($u['status']) ?></td>
      <td><?= htmlspecialchars($u['created_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
