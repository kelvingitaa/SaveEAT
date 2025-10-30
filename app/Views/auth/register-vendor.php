<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Vendor Registration</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?= BASE_URL ?>/register/vendor" enctype="multipart/form-data">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        
                        <h5 class="mb-3">Business Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Business Name *</label>
                                <input type="text" name="business_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Type</label>
                                <select name="business_type" class="form-select">
                                    <option value="restaurant">Restaurant</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="cafe">Cafe</option>
                                    <option value="bakery">Bakery</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Business Address *</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h5 class="mb-3">Contact Person</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Name *</label>
                                <input type="text" name="contact_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h5 class="mb-3">Business License</h5>
                        <div class="mb-3">
                            <label class="form-label">Upload Business License *</label>
                            <input type="file" name="license_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="form-text">Upload your business license certificate (PDF, JPG, PNG)</div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> Your registration will be reviewed and you'll need to be verified before you can start listing food items.
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Register as Vendor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';