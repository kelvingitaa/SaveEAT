
<?php
ob_start();
?>
<style>
  .dashboard-header { background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%); color: #fff; padding: 2rem 1rem; border-radius: 1rem; margin-bottom: 2rem; }
  .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
  .dashboard-card { background: #f8fafc; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.08); padding: 1.5rem; display: flex; align-items: center; gap: 1rem; }
  .dashboard-card .icon { font-size: 2rem; width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 0.75rem; }
  .dashboard-card .stat-title { font-weight: 600; color: #2563eb; margin-bottom: 0.25rem; }
  .dashboard-card .stat-value { font-size: 1.5rem; font-weight: bold; }
  .dashboard-card .stat-change.positive { color: #22c55e; }
  .dashboard-card .stat-change.negative { color: #f97316; }
  .dashboard-main { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
  @media (max-width: 900px) { .dashboard-main { grid-template-columns: 1fr; } }
  .dashboard-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 1.5rem; margin-bottom: 2rem; }
  .dashboard-sidebar { background: #f1f5f9; border-radius: 1rem; padding: 1.5rem; }
</style>
<div class="dashboard-stats">
  <div class="dashboard-card">
    <span class="icon" style="background:#2563eb1a;color:#2563eb;"><i class="bi bi-people"></i></span>
    <div>
      <div class="stat-title">Total Users</div>
      <div class="stat-value"><?= $userCount ?? '0' ?></div>
      <div class="stat-change positive">+12%</div>
    </div>
  </div>
  <div class="dashboard-card">
    <span class="icon" style="background:#22c55e1a;color:#22c55e;"><i class="bi bi-currency-dollar"></i></span>
    <div>
      <div class="stat-title">Revenue</div>
      <div class="stat-value">$45,678</div>
      <div class="stat-change positive">+8.2%</div>
    </div>
  </div>
  <div class="dashboard-card">
    <span class="icon" style="background:#a21caf1a;color:#a21caf;"><i class="bi bi-activity"></i></span>
    <div>
      <div class="stat-title">Active Sessions</div>
      <div class="stat-value">2,456</div>
      <div class="stat-change positive">+15%</div>
    </div>
  </div>
  <div class="dashboard-card">
    <span class="icon" style="background:#f973161a;color:#f97316;"><i class="bi bi-eye"></i></span>
    <div>
      <div class="stat-title">Page Views</div>
      <div class="stat-value">34,567</div>
      <div class="stat-change negative">-2.4%</div>
    </div>
  </div>
</div>
<div class="row">
  <aside class="col-lg-3 col-md-4 mb-4">
    <nav class="bg-white rounded shadow-sm p-3 sticky-top" style="top:80px;">
      <h5 class="mb-3 text-primary">Admin Menu</h5>
      <ul class="nav flex-column gap-2">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin">Dashboard Overview</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users">User Management</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors">Vendor Management</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories">Category Management</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/food">Food Item Management</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders">Order Management</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/logs">Audit Logs</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/settings">System Settings</a></li>
      </ul>
    </nav>
  </aside>
  <main class="col-lg-9 col-md-8">
    <div class="dashboard-section mb-4">
      <h2 class="h5 mb-3">Revenue Chart</h2>
      <div class="bg-light rounded p-4 text-center text-muted">[Revenue chart placeholder]</div>
    </div>
    <div class="dashboard-section mb-4">
      <h2 class="h5 mb-3">Users Table</h2>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach (($users ?? []) as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($users)): ?><div class="text-muted">No users found.</div><?php endif; ?>
      </div>
    </div>
    <div class="dashboard-section">
      <h2 class="h6">Quick Actions</h2>
      <button class="btn btn-primary mb-2" onclick="alert('Add User')">Add User</button>
      <button class="btn btn-outline-primary" onclick="alert('Export Data')">Export Data</button>
    </div>
    <div class="dashboard-section">
      <h2 class="h6">System Status</h2>
      <div class="bg-white rounded p-3 mb-2">All systems operational</div>
    </div>
    <div class="dashboard-section">
      <h2 class="h6">Recent Activity</h2>
      <ul class="list-unstyled">
        <li><span class="text-success">&#9679;</span> User John registered</li>
        <li><span class="text-primary">&#9679;</span> Vendor Jane added new item</li>
        <li><span class="text-warning">&#9679;</span> Order #1234 completed</li>
      </ul>
    </div>
  </main>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
