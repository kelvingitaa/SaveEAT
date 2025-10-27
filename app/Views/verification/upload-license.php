<?php
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Vendor License Verification</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Verification Requirements</h6>
                        <p class="mb-2">To operate as a food vendor on SaveEat, you must provide a valid business license.</p>
                        <ul class="mb-0">
                            <li>Upload a clear photo or scan of your business license</li>
                            <li>File must be in PDF, JPG, or PNG format</li>
                            <li>Maximum file size: 5MB</li>
                            <li>Verification typically takes 1-2 business days</li>
                        </ul>
                    </div>

                    <?php if ($verification && $verification['verification_status'] === 'approved'): ?>
                        <div class="alert alert-success">
                            <h6><i class="bi bi-check-circle"></i> Verification Approved</h6>
                            <p class="mb-0">Your vendor license has been verified and approved on 
                                <?= date('M j, Y', strtotime($verification['verified_at'])) ?>.</p>
                        </div>
                    <?php elseif ($verification && $verification['verification_status'] === 'pending'): ?>
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-clock-history"></i> Verification Pending</h6>
                            <p class="mb-0">Your license document has been submitted and is under review.</p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/verification/process-license" enctype="multipart/form-data">
                        <input type="hidden" name="_csrf" value="<?= $token ?>">
                        
                        <div class="mb-4">
                            <label class="form-label">Business License Document *</label>
                            <input type="file" name="license_document" class="form-control" 
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="form-text">
                                Accepted formats: PDF, JPG, PNG (Max: 5MB)
                            </div>
                        </div>

                        <?php if ($verification && $verification['license_document_path']): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Document:</label>
                                <div class="border rounded p-3 bg-light">
                                    <i class="bi bi-file-earmark-pdf"></i> 
                                    <a href="<?= BASE_URL . $verification['license_document_path'] ?>" target="_blank">
                                        View Uploaded Document
                                    </a>
                                    <small class="text-muted d-block">Uploaded on: <?= date('M j, Y', strtotime($verification['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-upload"></i> Upload License Document
                            </button>
                            <a href="<?= BASE_URL ?>/verification/status" class="btn btn-outline-primary">
                                Check Verification Status
                            </a>
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