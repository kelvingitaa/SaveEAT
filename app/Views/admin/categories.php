<?php
<<<<<<< HEAD
$token = App\Core\CSRF::token();
ob_start();
?>
<div class="category-table-section" style="background:#fff;border-radius:1rem;box-shadow:0 2px 8px rgba(30,64,175,0.06);padding:2rem;margin-bottom:2rem;">
  <div class="category-table-header d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold text-primary mb-0"><i class="bi bi-tags"></i> Category Management</h2>
    <button class="btn btn-primary"><i class="bi bi-plus"></i> Add Category</button>
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
          <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/items"><i class="bi bi-basket"></i> Food Items</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        </ul>
      </div>
    </nav>
    <!-- Main Content -->
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-tags"></i> Category Management</h1>
      </div>

      <!-- Flash Messages -->
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

      <!-- Add Category Form -->
      <div class="card mb-4">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="bi bi-plus"></i> Add New Category</h5>
        </div>
        <div class="card-body">
          <form method="post" class="row g-3" action="<?= BASE_URL ?>/admin/categories">
            <input type="hidden" name="_csrf" value="<?= $token ?>">
            <div class="col-md-4">
              <label class="form-label">Category Name</label>
              <input type="text" name="name" class="form-control" placeholder="Enter category name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Description</label>
              <input type="text" name="description" class="form-control" placeholder="Enter description (optional)">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Add Category</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="card">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="bi bi-list"></i> All Categories</h5>
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
                  <tr id="category-row-<?= $c['id'] ?>">
                    <td><?= (int)$c['id'] ?></td>
                    <td>
                      <span id="category-name-<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></span>
                      <div id="edit-form-<?= $c['id'] ?>" class="edit-form" style="display: none;">
                        <form method="post" action="<?= BASE_URL ?>/admin/categories/update" class="row g-2">
                          <input type="hidden" name="_csrf" value="<?= $token ?>">
                          <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                          <div class="col-8">
                            <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($c['name']) ?>" required>
                          </div>
                          <div class="col-4">
                            <button type="submit" class="btn btn-success btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditForm(<?= $c['id'] ?>)">Cancel</button>
                          </div>
                        </form>
                      </div>
                    </td>
                    <td>
                      <span id="category-desc-<?= $c['id'] ?>"><?= htmlspecialchars($c['description']) ?></span>
                      <div id="edit-desc-form-<?= $c['id'] ?>" class="edit-form" style="display: none;">
                        <form method="post" action="<?= BASE_URL ?>/admin/categories/update" class="row g-2">
                          <input type="hidden" name="_csrf" value="<?= $token ?>">
                          <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                          <div class="col-8">
                            <input type="text" name="description" class="form-control form-control-sm" value="<?= htmlspecialchars($c['description']) ?>">
                          </div>
                          <div class="col-4">
                            <button type="submit" class="btn btn-success btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditForm(<?= $c['id'] ?>)">Cancel</button>
                          </div>
                        </form>
                      </div>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="showEditForm(<?= $c['id'] ?>, '<?= htmlspecialchars($c['name']) ?>')" title="Edit Name">
                          <i class="bi bi-pencil"></i> Name
                        </button>
                        <button class="btn btn-outline-info" onclick="showDescEditForm(<?= $c['id'] ?>, '<?= htmlspecialchars($c['description']) ?>')" title="Edit Description">
                          <i class="bi bi-pencil"></i> Desc
                        </button>
                        <form method="post" action="<?= BASE_URL ?>/admin/categories/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                          <input type="hidden" name="_csrf" value="<?= $token ?>">
                          <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                          <button type="submit" class="btn btn-outline-danger" title="Delete Category">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                      <i class="bi bi-inbox display-4 d-block mb-2"></i>
                      No categories found. Add your first category above.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
>>>>>>> fbe2f2352f51f03e7ea1f2afe40b2cc8d8bb19ff
  </div>
  <form method="post" class="row g-2 mb-3" action="<?= BASE_URL ?>/admin/categories">
    <input type="hidden" name="_csrf" value="<?= $token ?>">
    <div class="col-md-4"><input name="name" class="form-control" placeholder="Category name" required></div>
    <div class="col-md-6"><input name="description" class="form-control" placeholder="Description"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
  </form>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Actions</th>
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
        <?php if (empty($categories)): ?><tr><td colspan="4" class="text-center text-muted">No categories found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
function showEditForm(categoryId, currentName) {
  // Hide all other edit forms first
  document.querySelectorAll('.edit-form').forEach(form => {
    form.style.display = 'none';
  });
  
  // Show name edit form
  document.getElementById('edit-form-' + categoryId).style.display = 'block';
  document.getElementById('category-name-' + categoryId).style.display = 'none';
}

function showDescEditForm(categoryId, currentDesc) {
  // Hide all other edit forms first
  document.querySelectorAll('.edit-form').forEach(form => {
    form.style.display = 'none';
  });
  
  // Show description edit form
  document.getElementById('edit-desc-form-' + categoryId).style.display = 'block';
  document.getElementById('category-desc-' + categoryId).style.display = 'none';
}

function hideEditForm(categoryId) {
  // Hide both edit forms and show display text
  document.getElementById('edit-form-' + categoryId).style.display = 'none';
  document.getElementById('edit-desc-form-' + categoryId).style.display = 'none';
  document.getElementById('category-name-' + categoryId).style.display = 'inline';
  document.getElementById('category-desc-' + categoryId).style.display = 'inline';
}
</script>

<style>
.edit-form {
  background: #f8f9fa;
  padding: 8px;
  border-radius: 4px;
  margin-top: 4px;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';