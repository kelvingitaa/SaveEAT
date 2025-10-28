<?php
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
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/vendor"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/vendor/items"><i class="bi bi-basket"></i> Food Items</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/donations"><i class="bi bi-heart"></i> Donations</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-basket"></i> My Food Items</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= BASE_URL ?>/vendor" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

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

            <!-- Add Item Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-plus-circle"></i> Add New Food Item</h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/vendor/items/store">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Food Name *</label>
                                <input name="name" class="form-control" placeholder="e.g., Margherita Pizza" required>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Price (KSh) *</label>
                                <input name="price" type="number" step="0.01" min="0" class="form-control" placeholder="0.00" required>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Discount %</label>
                                <input name="discount_percent" type="number" min="0" max="90" class="form-control" placeholder="0" value="0">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Expiry Date *</label>
                                <input name="expiry_date" type="date" class="form-control" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                       value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                <small class="form-text text-muted">
                                    Must be at least 24 hours from now for food safety
                                </small>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Stock *</label>
                                <input name="stock" type="number" min="1" class="form-control" placeholder="0" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Food Image</label>
                                <input name="image" type="file" class="form-control" accept="image/*">
                                <small class="form-text text-muted">JPG, PNG, GIF (Max: 2MB)</small>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" placeholder="Describe your food item..." rows="2"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Food Safety Notice:</strong> All food items must be safe to eat for at least 24 hours. 
                                    Items expiring sooner will be automatically removed.
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-plus-circle"></i> Add Food Item
                                </button>
                                <a href="<?= BASE_URL ?>/vendor/items" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Items List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul"></i> My Food Items (<?= count($items) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Stock</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td><?= (int)$it['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($it['name']) ?></strong>
                                        <?php if ($it['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($it['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($it['category_name']) ?></td>
                                    <td>
                                        <strong>KSh <?= number_format($it['price'], 2) ?></strong>
                                        <?php if ($it['discount_percent'] > 0): ?>
                                            <br>
                                            <small class="text-success">
                                                -<?= (int)$it['discount_percent'] ?>% 
                                                (KSh <?= number_format($it['price'] * (1 - $it['discount_percent']/100), 2) ?>)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($it['discount_percent'] > 0): ?>
                                            <span class="badge bg-warning"><?= (int)$it['discount_percent'] ?>%</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $it['stock'] > 10 ? 'success' : ($it['stock'] > 0 ? 'warning' : 'danger') ?>">
                                            <?= (int)$it['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $expiryDate = new DateTime($it['expiry_date']);
                                            $today = new DateTime();
                                            $daysUntilExpiry = $today->diff($expiryDate)->days;
                                            
                                            if ($expiryDate < $today): ?>
                                                <span class="badge bg-danger" title="Expired">
                                                    <?= $expiryDate->format('M j, Y') ?>
                                                </span>
                                            <?php elseif ($daysUntilExpiry <= 1): ?>
                                                <span class="badge bg-warning" title="Expiring soon">
                                                    <?= $expiryDate->format('M j, Y') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <?= $expiryDate->format('M j, Y') ?>
                                                </span>
                                            <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $it['status'] === 'active' ? 'success' : 
                                            ($it['status'] === 'expired' ? 'danger' : 'secondary')
                                        ?>">
                                            <?= ucfirst($it['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editItemModal<?= $it['id'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" action="<?= BASE_URL ?>/vendor/items/delete" class="d-inline">
                                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No food items yet. Add your first item above!
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.badge {
    font-size: 0.75rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';