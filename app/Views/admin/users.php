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
  .edit-form { display: none; }
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
              <!-- Edit Button -->
              <button class="btn btn-sm btn-outline-secondary" onclick="showEditForm(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', '<?= htmlspecialchars($u['email']) ?>', '<?= htmlspecialchars($u['role']) ?>', '<?= htmlspecialchars($u['status']) ?>')">Edit</button>
              
              <!-- Suspend/Activate Button -->
              <form method="post" action="<?= BASE_URL ?>/admin/users/toggle-status" style="display: inline;">
                <input type="hidden" name="_csrf" value="<?= $token ?>">
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-<?= $u['status'] === 'active' ? 'warning' : 'success' ?>">
                  <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                </button>
              </form>
              
              <!-- Delete Button -->
              <form method="post" action="<?= BASE_URL ?>/admin/users/delete" style="display: inline;">
                <input type="hidden" name="_csrf" value="<?= $token ?>">
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</button>
              </form>
            </td>
          </tr>
          <!-- Edit Form (hidden by default) -->
          <tr id="edit-form-<?= $u['id'] ?>" class="edit-form">
            <td colspan="5">
              <div class="p-3 border rounded bg-light">
                <h5>Edit User</h5>
                <form method="post" action="<?= BASE_URL ?>/admin/users/update">
                  <input type="hidden" name="_csrf" value="<?= $token ?>">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Name</label>
                      <input type="text" name="name" class="form-control" id="edit-name-<?= $u['id'] ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Email</label>
                      <input type="email" name="email" class="form-control" id="edit-email-<?= $u['id'] ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Role</label>
                      <select name="role" class="form-select" id="edit-role-<?= $u['id'] ?>" required>
                        <option value="consumer">Consumer</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Admin</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select" id="edit-status-<?= $u['id'] ?>" required>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <button type="submit" class="btn btn-success">Update User</button>
                      <button type="button" class="btn btn-secondary" onclick="hideEditForm(<?= $u['id'] ?>)">Cancel</button>
                    </div>
                  </div>
                </form>
              </div>
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

function showEditForm(userId, name, email, role, status) {
  // Hide all other edit forms first
  document.querySelectorAll('.edit-form').forEach(form => {
    form.style.display = 'none';
  });
  
  // Set form values
  document.getElementById('edit-name-' + userId).value = name;
  document.getElementById('edit-email-' + userId).value = email;
  document.getElementById('edit-role-' + userId).value = role;
  document.getElementById('edit-status-' + userId).value = status;
  
  // Show this edit form
  document.getElementById('edit-form-' + userId).style.display = 'table-row';
}

function hideEditForm(userId) {
  document.getElementById('edit-form-' + userId).style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';