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
            <option value="<?= (int)$c['id'] ?>" <?= (isset($_GET['category_id']) && (int)$_GET['category_id'] === (int)$c['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 col-sm-6">
        <select name="sort" class="form-select form-select-lg" onchange="this.form.submit()">
          <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
          <option value="price_low" <?= ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_high" <?= ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
          <option value="discount" <?= ($_GET['sort'] ?? '') === 'discount' ? 'selected' : '' ?>>Best Discount</option>
          <option value="expiry" <?= ($_GET['sort'] ?? '') === 'expiry' ? 'selected' : '' ?>>Expiring Soon</option>
        </select>
      </div>
      <div class="col-md-2 col-sm-6">
        <button class="btn btn-primary btn-lg w-100">Find Food</button>
      </div>
    </form>
  </div>
</section>

<div class="container">
  <h2 class="mb-4 text-center text-dark">Available Food Items</h2>
  
  <?php if (!empty($_GET['q']) || !empty($_GET['category_id'])): ?>
    <div class="alert alert-info">
      Showing results 
      <?php if (!empty($_GET['q'])): ?>for "<?= htmlspecialchars($_GET['q']) ?>"<?php endif; ?>
      <?php if (!empty($_GET['category_id'])): ?>
        <?php $catName = ''; foreach ($categories as $c) { if ($c['id'] == $_GET['category_id']) $catName = $c['name']; } ?>
        in <?= htmlspecialchars($catName) ?>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/consumer" class="float-end">Clear filters</a>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($items as $it): ?>
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 shadow border-0 food-item-card">
          <!-- Discount Badge -->
          <?php if ($it['discount_percent'] > 0): ?>
            <div class="position-absolute top-0 start-0 m-2">
              <span class="badge bg-danger"><?= $it['discount_percent'] ?>% OFF</span>
            </div>
          <?php endif; ?>

          <!-- Expiry Warning Badge -->
          <?php
          $expiryDate = new DateTime($it['expiry_date']);
          $today = new DateTime();
          $hoursLeft = ($expiryDate->getTimestamp() - $today->getTimestamp()) / 3600;
          ?>
          <?php if ($hoursLeft <= 24): ?>
            <div class="position-absolute top-0 end-0 m-2">
              <span class="badge bg-warning text-dark">Expires Soon!</span>
            </div>
          <?php endif; ?>

          <!-- Food Image -->
          <?php if (!empty($it['image_path'])): ?>
            <img src="<?= htmlspecialchars($it['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($it['name']) ?>" style="height:180px;object-fit:cover;">
          <?php else: ?>
            <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary text-white" style="height:180px;">
              <i class="bi bi-image display-4"></i>
            </div>
          <?php endif; ?>

          <div class="card-body d-flex flex-column">
            <h5 class="card-title text-primary mb-1"><?= htmlspecialchars($it['name']) ?></h5>
            
            <!-- VENDOR INFORMATION - ADDED THIS SECTION -->
            <div class="mb-2">
              <small class="text-muted">
                <i class="bi bi-shop"></i> <strong><?= htmlspecialchars($it['vendor_name'] ?? 'Vendor') ?></strong>
              </small>
              <br>
              <small class="text-muted">
                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($it['vendor_location'] ?? 'Nairobi') ?>
              </small>
            </div>

            <!-- Category -->
            <p class="card-text small mb-1">
              <i class="bi bi-tag"></i> 
              <span class="badge bg-secondary"><?= htmlspecialchars($it['category_name'] ?? 'Uncategorized') ?></span>
            </p>

            <!-- Description -->
            <?php if (!empty($it['description'])): ?>
              <p class="card-text small text-muted mb-2"><?= htmlspecialchars($it['description']) ?></p>
            <?php endif; ?>

            <!-- STORAGE INSTRUCTIONS - ADDED THIS SECTION -->
            <?php if (!empty($it['storage_instructions'])): ?>
              <div class="mb-2">
                <small class="text-info">
                  <i class="bi bi-info-circle"></i> 
                  <?= htmlspecialchars($it['storage_instructions']) ?>
                </small>
              </div>
            <?php endif; ?>

            <!-- Expiry Info -->
            <div class="mb-2">
              <small class="<?= $hoursLeft <= 24 ? 'text-danger' : 'text-success' ?>">
                <i class="bi bi-clock"></i> 
                <?php if ($hoursLeft <= 24): ?>
                  Expires in <?= round($hoursLeft) ?> hours!
                <?php else: ?>
                  Expires in <?= round($hoursLeft / 24) ?> days
                <?php endif; ?>
              </small>
            </div>

            <!-- Pricing -->
            <div class="mb-3">
              <?php if ($it['discount_percent'] > 0): ?>
                <span class="text-success fw-bold fs-5">KSh <?= number_format($it['price'] * (1 - $it['discount_percent']/100), 0) ?></span>
                <br>
                <small class="text-muted text-decoration-line-through">KSh <?= number_format($it['price'], 0) ?></small>
                <span class="badge bg-success ms-1">Save <?= $it['discount_percent'] ?>%</span>
              <?php else: ?>
                <span class="text-success fw-bold fs-5">KSh <?= number_format($it['price'], 0) ?></span>
              <?php endif; ?>
            </div>

            <!-- Stock Info -->
            <div class="mb-3">
              <small class="text-muted">
                <i class="bi bi-box"></i> 
                <?= $it['stock'] > 5 ? 'In stock' : 'Only ' . $it['stock'] . ' left!' ?>
              </small>
            </div>

            <!-- Add to Cart Form -->
            <form method="post" action="<?= BASE_URL ?>/consumer/cart/add" class="mt-auto">
              <input type="hidden" name="_csrf" value="<?= $token ?>">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <div class="input-group">
                <input type="number" name="qty" min="1" max="<?= $it['stock'] ?>" value="1" class="form-control" style="max-width: 80px;">
                <button class="btn btn-success" <?= $it['stock'] < 1 ? 'disabled' : '' ?>>
                  <i class="bi bi-cart-plus"></i> Add
                </button>
              </div>
              <?php if ($it['stock'] < 1): ?>
                <small class="text-danger">Out of stock</small>
              <?php endif; ?>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    
    <?php if (empty($items)): ?>
      <div class="col-12 text-center py-5">
        <i class="bi bi-search display-1 text-muted"></i>
        <h3 class="text-muted mt-3">No food items found</h3>
        <p class="text-muted">Try adjusting your search criteria or check back later for new items.</p>
        <a href="<?= BASE_URL ?>/consumer" class="btn btn-primary">Clear Filters</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.food-item-card {
  transition: transform 0.2s ease-in-out;
}
.food-item-card:hover {
  transform: translateY(-5px);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';