<?php
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$error = Session::flash('error');
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/vendor"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/vendor/items"><i class="bi bi-basket"></i> Food Items</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/donations"><i class="bi bi-heart"></i> Donations</a></li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-heart"></i> Schedule Donation</h1>
                <a href="<?= BASE_URL ?>/donations" class="btn btn-outline-secondary">Back to Donations</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Donation Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= BASE_URL ?>/donations/store">
                                <input type="hidden" name="_csrf" value="<?= $token ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Food Item *</label>
                                        <select name="food_item_id" class="form-select" required>
                                            <option value="">Select Food Item</option>
                                            <?php foreach ($availableItems as $item): ?>
                                                <option value="<?= $item['id'] ?>">
                                                    <?= htmlspecialchars($item['name']) ?> 
                                                    (Stock: <?= $item['stock'] ?>, Expires: <?= date('M j', strtotime($item['expiry_date'])) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Shelter *</label>
                                        <select name="shelter_id" class="form-select" required>
                                            <option value="">Select Shelter</option>
                                            <?php foreach ($shelters as $shelter): ?>
                                                <option value="<?= $shelter['id'] ?>">
                                                    <?= htmlspecialchars($shelter['shelter_name']) ?> - <?= htmlspecialchars($shelter['location']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Quantity *</label>
                                        <input type="number" name="quantity" class="form-control" min="1" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Donation Date *</label>
                                        <input type="date" name="donation_date" class="form-control" 
                                               value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea name="notes" class="form-control" rows="3" 
                                                  placeholder="Any special instructions or notes..."></textarea>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i>
                                            By scheduling this donation, you agree to have the food items ready for pickup/delivery on the specified date.
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-heart-fill"></i> Schedule Donation
                                        </button>
                                        <a href="<?= BASE_URL ?>/donations" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0"><i class="bi