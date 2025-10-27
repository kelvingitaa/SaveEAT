
<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container-fluid">
  <div class="row">
    <!-- Sidepanel -->
    <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4" style="min-height:100vh;">
      <div class="sidebar-sticky">
        <ul class="nav flex-column">
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/dashboard"><i class="bi bi-house"></i> Dashboard</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/users"><i class="bi bi-people"></i> Users</a></li>
          <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/items"><i class="bi bi-basket"></i> Food Items</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        </ul>
      </div>
    </nav>
    <!-- Main Content -->
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-tags"></i> Category Management</h1>
        <button class="btn btn-primary"><i class="bi bi-plus"></i> Add Category</button>
      </div>
      <!-- Add Category Form -->
      <div class="card mb-4">
        <div class="card-header bg-white">
          <form method="post" class="row g-2" action="<?= BASE_URL ?>/admin/categories">
            <input type="hidden" name="_csrf" value="<?= $token ?>">
            <div class="col-md-4"><input name="name" class="form-control" placeholder="Category name" required></div>
            <div class="col-md-6"><input name="description" class="form-control" placeholder="Description"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
          </form>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col">ID</th>
                  <th scope="col">Name</th>
                  <th scope="col">Description</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $c): ?>
                  <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['description']) ?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                <?php endforeach; ?>
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
