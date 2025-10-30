<?php
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Flash Messages Section -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <h2 class="mb-4"><i class="bi bi-cart3"></i> Your Shopping Cart</h2>
            
            <?php if (empty($items)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h3 class="mt-3">Your cart is empty</h3>
                    <p class="text-muted">Browse our delicious food items and add some to your cart!</p>
                    <a href="<?= BASE_URL ?>/consumer" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Discount</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $it): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <strong><?= htmlspecialchars($it['name']) ?></strong>
                                                        <?php if ($it['discount_percent'] > 0): ?>
                                                            <br>
                                                            <small class="text-success">
                                                                <i class="bi bi-tag"></i> <?= $it['discount_percent'] ?>% OFF
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($it['discount_percent'] > 0): ?>
                                                    <span class="text-success fw-bold">KSh <?= number_format($it['price'] * (1 - $it['discount_percent']/100), 0) ?></span>
                                                    <br>
                                                    <small class="text-muted text-decoration-line-through">KSh <?= number_format($it['price'], 0) ?></small>
                                                <?php else: ?>
                                                    <span class="fw-bold">KSh <?= number_format($it['price'], 0) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= BASE_URL ?>/consumer/cart/update" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="id" value="<?= $it['id'] ?>">
                                                    <div class="input-group input-group-sm" style="width: 120px;">
                                                        <input type="number" name="qty" min="1" max="10" value="<?= $it['qty'] ?>" class="form-control">
                                                        <button type="submit" class="btn btn-outline-primary" title="Update quantity">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <?php if ($it['discount_percent'] > 0): ?>
                                                    <span class="badge bg-success">Save <?= $it['discount_percent'] ?>%</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold text-success">KSh <?= number_format($it['line_total'], 0) ?></td>
                                            <td>
                                                <form method="post" action="<?= BASE_URL ?>/consumer/cart/remove" class="d-inline" onsubmit="return confirm('Remove this item from your cart?')">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="id" value="<?= $it['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                                        <td colspan="2" class="fw-bold fs-5 text-success">KSh <?= number_format($total, 0) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>/consumer" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left"></i> Continue Shopping
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Order Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Items:</span>
                                            <span><?= count($items) ?> item(s)</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span>KSh <?= number_format($total, 0) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Delivery:</span>
                                            <span class="text-success">FREE</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold fs-5">
                                            <span>Total:</span>
                                            <span class="text-success">KSh <?= number_format($total, 0) ?></span>
                                        </div>
                                        
                                        <form method="post" action="<?= BASE_URL ?>/consumer/checkout" class="mt-3">
                                            <input type="hidden" name="_csrf" value="<?= $token ?>">
                                            <button type="submit" class="btn btn-success btn-lg w-100">
                                                <i class="bi bi-lock"></i> Proceed to Checkout
                                            </button>
                                        </form>
                                        
                                        <div class="mt-2 text-center">
                                            <small class="text-muted">
                                                <i class="bi bi-phone"></i> You'll pay via Mobile Money
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.food-item-card {
    transition: transform 0.2s ease-in-out;
}
.food-item-card:hover {
    transform: translateY(-5px);
}
.alert {
    border-radius: 10px;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';