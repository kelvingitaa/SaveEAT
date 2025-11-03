<?php
ob_start();
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
?>
<style>
.admin-items-container { 
    min-height: calc(100vh - 200px);
    background: #f8fafc;
    padding: 2rem;
}
.item-table-section { 
    background: #fff; 
    border-radius: 1rem; 
    box-shadow: 0 2px 12px rgba(0,0,0,0.08); 
    padding: 2rem; 
    margin-bottom: 2rem; 
}
.item-table-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 1.5rem; 
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}
.item-table-header h2 { 
    margin: 0; 
    font-size: 1.5rem; 
    color: #1e293b;
    font-weight: 600;
}
.item-table-filters { 
    display: flex; 
    gap: 1rem; 
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.75rem;
}
.item-table-filters select, 
.item-table-filters input { 
    min-width: 140px; 
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}
.table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}
.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.35em 0.65em;
}
.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
}
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>

<div class="admin-items-container">
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

    <div class="item-table-section">
        <div class="item-table-header">
            <h2><i class="bi bi-basket me-2"></i> Food Item Management</h2>
            <button class="btn btn-primary"><i class="bi bi-plus me-2"></i> Add Item</button>
        </div>
        
        <form class="item-table-filters" method="get" action="<?= BASE_URL ?>/admin/items">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories ?? [] as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" <?= isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="expired" <?= isset($_GET['status']) && $_GET['status'] == 'expired' ? 'selected' : '' ?>>Expired</option>
            </select>
            
            <input type="text" name="q" class="form-control" placeholder="Search name/vendor" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search me-2"></i> Filter</button>
            <a href="<?= BASE_URL ?>/admin/items" class="btn btn-outline-secondary">Clear</a>
        </form>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($items ?? []) as $item): ?>
                        <tr>
                            <td class="fw-bold">#<?= (int)$item['id'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                <?php if (!empty($item['description'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($item['category_name'] ?? $item['category_id'] ?? '-') ?></span>
                            </td>
                            <td><?= htmlspecialchars($item['vendor_name'] ?? $item['vendor_id'] ?? '-') ?></td>
                            <td class="fw-bold text-success"><?= isset($item['price']) ? 'KSh ' . number_format($item['price'], 2) : '-' ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $item['status'] === 'active' ? 'success' : 
                                    ($item['status'] === 'inactive' ? 'warning' : 'danger')
                                ?> text-capitalize">
                                    <?= htmlspecialchars($item['status']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?= isset($item['created_at']) ? date('M j, Y', strtotime($item['created_at'])) : '-' ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit" onclick="alert('Edit Item <?= $item['id'] ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" title="Delete" onclick="if(confirm('Are you sure you want to delete this item?')) alert('Delete Item <?= $item['id'] ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-basket display-4 d-block mb-2"></i>
                                No food items found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';