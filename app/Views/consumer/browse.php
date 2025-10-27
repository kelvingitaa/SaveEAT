
<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<section class="py-5 bg-light mb-4 rounded shadow-sm">
  <div class="container text-center">
    <h1 class="display-5 fw-bold text-primary mb-3">Welcome to SaveEAT</h1>
    <p class="lead mb-4">Discover delicious food, save money, and reduce waste. Browse local vendors and add your favorites to your cart!</p>
    <form class="row g-2 justify-content-center" method="get" action="<?= BASE_URL ?>/consumer">
      <div class="col-md-4 col-sm-6">
        <input type="text" name="q" class="form-control form-control-lg" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search for food...">
      </div>
      <div class="col-md-3 col-sm-6">
        <select name="category_id" class="form-select form-select-lg">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (isset($_GET['category_id']) && (int)$_GET['category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 col-sm-6"><button class="btn btn-primary btn-lg w-100">Find Food</button></div>
    </form>
  </div>
</section>
<div class="container">
  <h2 class="mb-4 text-center text-dark">Featured Food Items</h2>
  <div class="row g-4">
    <?php foreach ($items as $it): ?>
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 shadow border-0">
          <?php if (!empty($it['image_path'])): ?>
            <img src="<?= BASE_URL . '/' . $it['image_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($it['name']) ?>" style="height:180px;object-fit:cover;">
          <?php else: ?>
            <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary text-white" style="height:180px;">
              <span>No Image</span>
            </div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title text-primary mb-1"><?= htmlspecialchars($it['name']) ?></h5>
            <p class="card-text small mb-1">Category: <span class="badge bg-secondary"><?= htmlspecialchars($it['category_name'] ?? '') ?></span></p>
            <p class="card-text mb-2">Price: <strong class="text-success">$<?= number_format($it['price'] * (1 - ($it['discount_percent'] ?? 0)/100), 2) ?></strong></p>
            <form method="post" action="<?= BASE_URL ?>/consumer/cart/add" class="mt-auto">
              <input type="hidden" name="_csrf" value="<?= $token ?>">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <div class="input-group">
                <input type="number" name="qty" min="1" value="1" class="form-control">
                <button class="btn btn-success">Add to Cart</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
      <div class="col-12 text-center text-muted">No items found.</div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
