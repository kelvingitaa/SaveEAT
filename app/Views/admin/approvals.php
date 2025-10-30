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
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> Users</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/shelters"><i class="bi bi-house-heart"></i> Shelters</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="<?= BASE_URL ?>/admin/approvals"><i class="bi bi-shield-check"></i> Registration Approvals</a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/reports"><i class="bi bi-graph-up"></i> Reports</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-shield-check"></i> Registration Approvals</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="badge bg-warning">Pending: <?= count($pendingVendors) + count($pendingDrivers) + count($pendingShelters) ?></span>
                    </div>
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

            <!-- Pending Vendors -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shop"></i> Pending Vendor Approvals
                        <span class="badge bg-dark ms-2"><?= count($pendingVendors) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($pendingVendors)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Business Name</th>
                                        <th>Owner</th>
                                        <th>Location</th>
                                        <th>Contact</th>
                                        <th>License Document</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingVendors as $vendor): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($vendor['business_name']) ?></td>
                                            <td><?= htmlspecialchars($vendor['owner_name']) ?><br><small><?= htmlspecialchars($vendor['email']) ?></small></td>
                                            <td><?= htmlspecialchars($vendor['location']) ?></td>
                                            <td><?= htmlspecialchars($vendor['contact_phone']) ?></td>
                                            <td>
                                                <?php if (!empty($vendor['license_document_path'])): ?>
                                                    <a href="<?= BASE_URL ?>/admin/view-document?type=vendor&id=<?= $vendor['id'] ?>&doc=license" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View License
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">No Document</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= BASE_URL ?>/admin/approve-vendor" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="vendor_id" value="<?= $vendor['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="showRejectForm('vendor', <?= $vendor['id'] ?>)">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle display-4"></i>
                            <p class="mt-3">No pending vendor approvals</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Drivers -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-truck"></i> Pending Driver Approvals
                        <span class="badge bg-dark ms-2"><?= count($pendingDrivers) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($pendingDrivers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Driver Name</th>
                                        <th>Contact</th>
                                        <th>Vehicle Type</th>
                                        <th>License Plate</th>
                                        <th>License Document</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingDrivers as $driver): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($driver['name']) ?><br><small><?= htmlspecialchars($driver['email']) ?></small></td>
                                            <td><?= htmlspecialchars($driver['phone']) ?><br><small><?= htmlspecialchars($driver['address']) ?></small></td>
                                            <td><?= htmlspecialchars($driver['vehicle_type']) ?></td>
                                            <td><?= htmlspecialchars($driver['license_plate']) ?></td>
                                            <td>
                                                <?php if (!empty($driver['license_file'])): ?>
                                                    <a href="<?= BASE_URL ?>/admin/view-document?type=driver&id=<?= $driver['id'] ?>&doc=license" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View License
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">No Document</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= BASE_URL ?>/admin/approve-driver" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="showRejectForm('driver', <?= $driver['id'] ?>)">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle display-4"></i>
                            <p class="mt-3">No pending driver approvals</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Shelters -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-house-heart"></i> Pending Shelter Approvals
                        <span class="badge bg-dark ms-2"><?= count($pendingShelters) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($pendingShelters)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Shelter Name</th>
                                        <th>Contact Person</th>
                                        <th>Location</th>
                                        <th>Capacity</th>
                                        <th>Contact Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingShelters as $shelter): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($shelter['shelter_name']) ?></td>
                                            <td><?= htmlspecialchars($shelter['contact_person']) ?><br><small><?= htmlspecialchars($shelter['email']) ?></small></td>
                                            <td><?= htmlspecialchars($shelter['location']) ?></td>
                                            <td><?= (int)$shelter['capacity'] ?></td>
                                            <td><?= htmlspecialchars($shelter['contact_phone']) ?></td>
                                            <td>
                                                <?php if (!empty($shelter['verification_document_path'])): ?>
                                                    <a href="<?= BASE_URL ?>/admin/view-document?type=shelter&id=<?= $shelter['id'] ?>&doc=verification" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View Documents
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">No Documents</span>
                                                <?php endif; ?>
                                                <form method="post" action="<?= BASE_URL ?>/admin/approve-shelter" class="d-inline">
                                                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                                                    <input type="hidden" name="shelter_id" value="<?= $shelter['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="showRejectForm('shelter', <?= $shelter['id'] ?>)">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle display-4"></i>
                            <p class="mt-3">No pending shelter approvals</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" method="post">
                    <input type="hidden" name="_csrf" value="<?= $token ?>">
                    <input type="hidden" id="reject_type" name="type">
                    <input type="hidden" id="reject_id" name="id">
                    
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="4" 
                                  placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        This action cannot be undone. The user will be notified of the rejection.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitRejectForm()">Reject Registration</button>
            </div>
        </div>
    </div>
</div>

<script>
function showRejectForm(type, id) {
    document.getElementById('reject_type').value = type;
    document.getElementById('reject_id').value = id;
    
    // Set form action based on type
    let form = document.getElementById('rejectForm');
    if (type === 'vendor') {
        form.action = '<?= BASE_URL ?>/admin/reject-vendor';
    } else if (type === 'driver') {
        form.action = '<?= BASE_URL ?>/admin/reject-driver';
    } else if (type === 'shelter') {
        form.action = '<?= BASE_URL ?>/admin/reject-shelter';
    }
    
    // Clear previous reason
    document.getElementById('reject_reason').value = '';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function submitRejectForm() {
    document.getElementById('rejectForm').submit();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>