<?php
use App\Core\CSRF;
$token = CSRF::token();
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
    <button class="btn btn-primary" onclick="showAddUserForm()">Add User</button>
  </div>
  
  <!-- Add User Form (initially hidden) -->
  <div id="addUserForm" class="mb-4 p-3 border rounded" style="display: none;">
    <h4>Add New User</h4>
    <form method="post" action="<?= BASE_URL ?>/admin/users/create">
      <input type="hidden" name="_csrf" value="<?= $token ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="consumer">Consumer</option>
            <option value="vendor">Vendor</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-success">Create User</button>
          <button type="button" class="btn btn-secondary" onclick="hideAddUserForm()">Cancel</button>
        </div>
      </div>
    </form>
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

<script>
function showAddUserForm() {
  document.getElementById('addUserForm').style.display = 'block';
}

function hideAddUserForm() {
  document.getElementById('addUserForm').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';