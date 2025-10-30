<?php
use App\Core\Session;

$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">
                        <i class="bi bi-truck"></i> Order Tracking
                        <small class="float-end">Order #<?= $orderId ?></small>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($delivery): ?>
                        <!-- Delivery Timeline -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4>Delivery Progress</h4>
                                <div class="timeline">
                                    <?php
                                    $statuses = [
                                        'pending_assignment' => ['icon' => '⏳', 'label' => 'Waiting for Driver', 'description' => 'Looking for available delivery driver'],
                                        'assigned' => ['icon' => '👨‍💼', 'label' => 'Driver Assigned', 'description' => 'Driver has been assigned to your order'],
                                        'vendor_confirmed' => ['icon' => '🏪', 'label' => 'Vendor Confirmed', 'description' => 'Vendor has confirmed your order'],
                                        'picked_up' => ['icon' => '📦', 'label' => 'Picked Up', 'description' => 'Driver has collected your order from the vendor'],
                                        'in_transit' => ['icon' => '🚗', 'label' => 'On the Way', 'description' => 'Your order is on the way to you'],
                                        'delivered' => ['icon' => '✅', 'label' => 'Delivered', 'description' => 'Order has been delivered to you'],
                                        'completed' => ['icon' => '🎉', 'label' => 'Completed', 'description' => 'Order completed successfully']
                                    ];
                                    
                                    $currentStatus = $delivery['status'];
                                    ?>
                                    
                                    <?php foreach ($statuses as $status => $info): ?>
                                        <?php
                                        $isCompleted = array_search($status, array_keys($statuses)) < array_search($currentStatus, array_keys($statuses));
                                        $isCurrent = $status === $currentStatus;
                                        $isFuture = array_search($status, array_keys($statuses)) > array_search($currentStatus, array_keys($statuses));
                                        ?>
                                        
                                        <div class="timeline-item <?= $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'future') ?>">
                                            <div class="timeline-icon">
                                                <?= $info['icon'] ?>
                                            </div>
                                            <div class="timeline-content">
                                                <h5><?= $info['label'] ?></h5>
                                                <p><?= $info['description'] ?></p>
                                                <?php if ($isCurrent): ?>
                                                    <div class="progress mt-2">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                             style="width: <?= (array_search($status, array_keys($statuses)) + 1) * (100 / count($statuses)) ?>%">
                                                            In Progress
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Driver Information -->
                        <?php if ($delivery['driver_name']): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-person-badge"></i> Your Driver
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="bi bi-person-circle display-4 text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5><?= htmlspecialchars($delivery['driver_name']) ?></h5>
                                                    <p class="mb-1">
                                                        <i class="bi bi-telephone"></i> 
                                                        <?= htmlspecialchars($delivery['driver_phone'] ?? 'Contact info available soon') ?>
                                                    </p>
                                                    <p class="mb-0">
                                                        <i class="bi bi-truck"></i> 
                                                        <?= htmlspecialchars($delivery['vehicle_type'] ?? 'Vehicle') ?> - 
                                                        <?= htmlspecialchars($delivery['license_plate'] ?? '') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-info-circle"></i> Delivery Details
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Status:</strong> 
                                                <span class="badge bg-<?= 
                                                    $delivery['status'] === 'delivered' ? 'success' : 
                                                    ($delivery['status'] === 'in_transit' ? 'primary' : 'warning')
                                                ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                                                </span>
                                            </p>
                                            <?php if ($delivery['pickup_time']): ?>
                                                <p><strong>Pickup Time:</strong> 
                                                    <?= date('M j, g:i A', strtotime($delivery['pickup_time'])) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($delivery['delivery_time']): ?>
                                                <p><strong>Delivery Time:</strong> 
                                                    <?= date('M j, g:i A', strtotime($delivery['delivery_time'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Auto-refresh for real-time updates -->
                        <div class="text-center">
                            <div class="alert alert-info">
                                <i class="bi bi-arrow-clockwise"></i>
                                <strong>Live Tracking:</strong> This page updates automatically every 30 seconds
                                <span id="lastUpdate" class="ms-2"></span>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-question-circle display-1 text-muted"></i>
                            <h3 class="mt-3 text-muted">Delivery Not Found</h3>
                            <p class="text-muted">We couldn't find tracking information for this order.</p>
                            <a href="<?= BASE_URL ?>/consumer/orders" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Back to Orders
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 30px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: -30px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item.completed::before {
    background: #28a745;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: -30px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    background: white;
    border: 2px solid #dee2e6;
    z-index: 2;
}

.timeline-item.completed .timeline-icon {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.timeline-item.current .timeline-icon {
    background: #007bff;
    color: white;
    border-color: #007bff;
    animation: pulse 2s infinite;
}

.timeline-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #dee2e6;
}

.timeline-item.completed .timeline-content {
    border-left-color: #28a745;
}

.timeline-item.current .timeline-content {
    border-left-color: #007bff;
    background: #e3f2fd;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<script>
// Auto-refresh the page every 30 seconds for real-time updates
setTimeout(() => {
    window.location.reload();
}, 30000);

// Update last refresh time
document.getElementById('lastUpdate').textContent = 'Last updated: ' + new Date().toLocaleTimeString();


</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';