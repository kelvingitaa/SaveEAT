<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Delivery Driver Registration</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?= BASE_URL ?>/register/driver" enctype="multipart/form-data">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        
                        <h5 class="mb-3">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required>
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
                            <div class="col-12">
                                <label class="form-label">Address *</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h5 class="mb-3">Vehicle Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Vehicle Type *</label>
                                <select name="vehicle_type" class="form-select" required>
                                    <option value="">Select Vehicle</option>
                                    <option value="bicycle">Bicycle</option>
                                    <option value="motorcycle">Motorcycle</option>
                                    <option value="car">Car</option>
                                    <option value="van">Van</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">License Plate</label>
                                <input type="text" name="license_plate" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Driver's License *</label>
                                <input type="file" name="license_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Upload your driver's license (PDF, JPG, PNG)</div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Note:</strong> Your registration will be reviewed before you can start accepting delivery assignments.
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-info btn-lg">Register as Driver</button>
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