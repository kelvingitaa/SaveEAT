
<?php
$token = App\Core\CSRF::token();
ob_start();
?>
<div class="category-table-section" style="background:#fff;border-radius:1rem;box-shadow:0 2px 8px rgba(30,64,175,0.06);padding:2rem;margin-bottom:2rem;">
  <div class="category-table-header d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold text-primary mb-0"><i class="bi bi-tags"></i> Category Management</h2>
    <button class="btn btn-primary"><i class="bi bi-plus"></i> Add Category</button>
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
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
