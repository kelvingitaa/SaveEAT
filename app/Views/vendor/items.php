<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<h3>My Items</h3>
<form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/vendor/items">
  <input type="hidden" name="_csrf" value="<?= $token ?>">
  <div class="row g-2">
    <div class="col-md-3"><input name="name" class="form-control" placeholder="Name" required></div>
    <div class="col-md-2">
      <select name="category_id" class="form-select">
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><input name="price" type="number" step="0.01" min="0" class="form-control" placeholder="Price" required></div>
    <div class="col-md-2"><input name="discount_percent" type="number" min="0" max="90" class="form-control" placeholder="Discount %"></div>
    <div class="col-md-2"><input name="expiry_date" type="date" class="form-control" required></div>
    <div class="col-md-1"><input name="stock" type="number" min="0" class="form-control" placeholder="Stock"></div>
    <div class="col-md-4"><input name="image" type="file" class="form-control"></div>
    <div class="col-md-12"><input name="description" class="form-control" placeholder="Description"></div>
    <div class="col-md-12"><button class="btn btn-success">Add Item</button></div>
  </div>
</form>
<hr>
<table class="table table-bordered table-striped">
  <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Discount</th><th>Stock</th><th>Expiry</th></tr></thead>
  <tbody>
  <?php foreach ($items as $it): ?>
    <tr>
      <td><?= (int)$it['id'] ?></td>
      <td><?= htmlspecialchars($it['name']) ?></td>
      <td><?= htmlspecialchars($it['category_name']) ?></td>
      <td>$<?= number_format($it['price'], 2) ?></td>
      <td><?= (int)$it['discount_percent'] ?>%</td>
      <td><?= (int)$it['stock'] ?></td>
      <td><?= htmlspecialchars($it['expiry_date']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
