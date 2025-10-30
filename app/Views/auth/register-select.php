<?php
ob_start();
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Join SaveEAT As:</h2>
            <div class="row g-4">
                <!-- Consumer Card -->
                <div class="col-md-3">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div style="font-size: 2.5rem;">🍽️</div>
                            <h6>Food Consumer</h6>
                            <small class="text-muted">Browse and order discounted meals</small>
                            <a href="<?= BASE_URL ?>/register/consumer" class="btn btn-primary btn-sm mt-2 w-100">Register</a>
                        </div>
                    </div>
                </div>
                
                <!-- Vendor Card -->
                <div class="col-md-3">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div style="font-size: 2.5rem;">🏪</div>
                            <h6>Food Vendor</h6>
                            <small class="text-muted">Hotel/Restaurant selling surplus food</small>
                            <a href="<?= BASE_URL ?>/register/vendor" class="btn btn-success btn-sm mt-2 w-100">Register</a>
                        </div>
                    </div>
                </div>
                
                <!-- Shelter Card -->
                <div class="col-md-3">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div style="font-size: 2.5rem;">🏠</div>
                            <h6>Shelter</h6>
                            <small class="text-muted">Receive food donations</small>
                            <a href="<?= BASE_URL ?>/register/shelter" class="btn btn-warning btn-sm mt-2 w-100">Register</a>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Driver Card -->
                <div class="col-md-3">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div style="font-size: 2.5rem;">🚚</div>
                            <h6>Delivery Driver</h6>
                            <small class="text-muted">Handle food deliveries</small>
                            <a href="<?= BASE_URL ?>/register/driver" class="btn btn-info btn-sm mt-2 w-100">Register</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p>Already have an account? <a href="<?= BASE_URL ?>/login">Login here</a></p>
                <div class="alert alert-warning mt-3">
                    <small>Admin registration is restricted. Contact system administrator.</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';