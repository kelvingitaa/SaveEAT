
<?php
use App\Core\CSRF;
$token = CSRF::token();
ob_start();
?>
<<<<<<< HEAD
<div class="vendor-table-section" style="background:#fff;border-radius:1rem;box-shadow:0 2px 8px rgba(30,64,175,0.06);padding:2rem;margin-bottom:2rem;">
  <div class="vendor-table-header d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold text-primary mb-0"><i class="bi bi-shop"></i> Vendor Management</h2>
    <button class="btn btn-primary" onclick="alert('Add Vendor')"><i class="bi bi-plus"></i> Add Vendor</button>
  </div>
  <form class="vendor-table-filters d-flex gap-2 mb-3" method="get" action="<?= BASE_URL ?>/admin/vendors">
=======
<style>
  .vendor-table-section { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(30,64,175,0.06); padding: 2rem; margin-bottom: 2rem; }
  .vendor-table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
  .vendor-table-header h2 { margin: 0; font-size: 1.5rem; color: #2563eb; }
  .vendor-table-filters { display: flex; gap: 1rem; }
  .vendor-table-filters select, .vendor-table-filters input { min-width: 120px; }
  .vendor-form { display: none; }
  .badge { font-size: 0.75rem; }
</style>
<div class="vendor-table-section">
  <div class="vendor-table-header">
    <h2>Vendor Management</h2>
    <button class="btn btn-primary" onclick="showAddVendorForm()">Add Vendor</button>
  </div>

  <!-- Add Vendor Form -->
  <div id="addVendorForm" class="mb-4 p-3 border rounded vendor-form">
    <h4>Add New Vendor</h4>
    <form method="post" action="<?= BASE_URL ?>/admin/vendors/create">
      <input type="hidden" name="_csrf" value="<?= $token ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Business Name</label>
          <input type="text" name="business_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact Phone</label>
          <input type="text" name="contact_phone" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">User ID</label>
          <input type="number" name="user_id" class="form-control" required placeholder="User ID for this vendor">
          <small class="form-text text-muted">User must have vendor role</small>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-success">Create Vendor</button>
          <button type="button" class="btn btn-secondary" onclick="hideAddVendorForm()">Cancel</button>
        </div>
      </div>
    </form>
  </div>

  <form class="vendor-table-filters mb-3" method="get" action="<?= BASE_URL ?>/admin/vendors">
>>>>>>> fbe2f2352f51f03e7ea1f2afe40b2cc8d8bb19ff
    <select name="status" class="form-select">
      <option value="">All Statuses</option>
      <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
      <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
    </select>
    <select name="approved" class="form-select">
      <option value="">All Approval</option>
      <option value="1" <?= ($_GET['approved'] ?? '') === '1' ? 'selected' : '' ?>>Approved</option>
      <option value="0" <?= ($_GET['approved'] ?? '') === '0' ? 'selected' : '' ?>>Pending Approval</option>
    </select>
    <input type="text" name="q" class="form-control" placeholder="Search business/location" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Filter</button>
  </form>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Business Name</th>
          <th>Location</th>
          <th>Contact</th>
          <th>Owner</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($vendors ?? []) as $v): ?>
          <tr>
            <td><?= (int)$v['id'] ?></td>
            <td><?= htmlspecialchars($v['business_name']) ?></td>
<<<<<<< HEAD
            <td><?= htmlspecialchars($v['location'] ?? '-') ?></td>
            <td><?= htmlspecialchars($v['contact'] ?? $v['contact_phone'] ?? '-') ?></td>
            <td><span class="badge bg-<?= ($v['status'] ?? ($v['approved'] ? 'active' : 'pending')) === 'active' ? 'success' : (($v['status'] ?? ($v['approved'] ? 'active' : 'pending')) === 'pending' ? 'warning' : 'danger') ?> text-capitalize">
              <?= htmlspecialchars($v['status'] ?? ($v['approved'] ? 'Active' : 'Pending')) ?></span></td>
            <td>
              <button class="btn btn-sm btn-outline-secondary" title="Edit" onclick="alert('Edit Vendor')"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="if(confirm('Delete vendor?'))alert('Delete Vendor')"><i class="bi bi-trash"></i></button>
=======
            <td><?= htmlspecialchars($v['location'] ?? '') ?></td>
            <td><?= htmlspecialchars($v['contact_phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($v['owner_name'] ?? 'N/A') ?></td>
            <td>
              <span class="badge bg-<?= 
                $v['status'] === 'active' ? 'success' : 
                ($v['status'] === 'suspended' ? 'danger' : 'warning') 
              ?>">
                <?= ucfirst($v['status']) ?>
                <?= !$v['approved'] ? ' (Pending)' : '' ?>
              </span>
            </td>
            <td>
              <?php if (!$v['approved']): ?>
                <form method="post" action="<?= BASE_URL ?>/admin/vendors/approve" class="d-inline">
                  <input type="hidden" name="_csrf" value="<?= $token ?>">
                  <input type="hidden" name="vendor_id" value="<?= (int)$v['id'] ?>">
                  <button class="btn btn-sm btn-success">Approve</button>
                </form>
              <?php endif; ?>
              
              <!-- Edit Button -->
              <button class="btn btn-sm btn-outline-secondary" onclick="showEditForm(<?= $v['id'] ?>, '<?= htmlspecialchars($v['business_name']) ?>', '<?= htmlspecialchars($v['location'] ?? '') ?>', '<?= htmlspecialchars($v['contact_phone'] ?? '') ?>')">Edit</button>
              
              <!-- Suspend/Activate Button -->
              <?php if ($v['approved']): ?>
                <form method="post" action="<?= BASE_URL ?>/admin/vendors/toggle-status" class="d-inline">
                  <input type="hidden" name="_csrf" value="<?= $token ?>">
                  <input type="hidden" name="vendor_id" value="<?= (int)$v['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-<?= 
                    $v['status'] === 'active' ? 'warning' : 'success' 
                  ?>">
                    <?= $v['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                  </button>
                </form>
              <?php endif; ?>
              
              <!-- Delete Button -->
              <form method="post" action="<?= BASE_URL ?>/admin/vendors/delete" class="d-inline">
                <input type="hidden" name="_csrf" value="<?= $token ?>">
                <input type="hidden" name="vendor_id" value="<?= (int)$v['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this vendor? This action cannot be undone.')">Delete</button>
              </form>
            </td>
          </tr>
          <!-- Edit Form (hidden by default) -->
          <tr id="edit-form-<?= $v['id'] ?>" class="vendor-form">
            <td colspan="7">
              <div class="p-3 border rounded bg-light">
                <h5>Edit Vendor</h5>
                <form method="post" action="<?= BASE_URL ?>/admin/vendors/update">
                  <input type="hidden" name="_csrf" value="<?= $token ?>">
                  <input type="hidden" name="vendor_id" value="<?= (int)$v['id'] ?>">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label">Business Name</label>
                      <input type="text" name="business_name" class="form-control" id="edit-business-<?= $v['id'] ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Location</label>
                      <input type="text" name="location" class="form-control" id="edit-location-<?= $v['id'] ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Contact Phone</label>
                      <input type="text" name="contact_phone" class="form-control" id="edit-phone-<?= $v['id'] ?>" required>
                    </div>
                    <div class="col-12">
                      <button type="submit" class="btn btn-success">Update Vendor</button>
                      <button type="button" class="btn btn-secondary" onclick="hideEditForm(<?= $v['id'] ?>)">Cancel</button>
                    </div>
                  </div>
                </form>
              </div>
>>>>>>> fbe2f2352f51f03e7ea1f2afe40b2cc8d8bb19ff
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($vendors)): ?><tr><td colspan="7" class="text-center text-muted">No vendors found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if (($pages ?? 1) > 1): ?>
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= BASE_URL ?>/admin/vendors?page=<?= $i ?>&<?= http_build_query($_GET) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>
<<<<<<< HEAD
=======

<script>
function showAddVendorForm() {
  document.getElementById('addVendorForm').style.display = 'block';
}

function hideAddVendorForm() {
  document.getElementById('addVendorForm').style.display = 'none';
}

function showEditForm(vendorId, businessName, location, contactPhone) {
  // Hide all other edit forms first
  document.querySelectorAll('.vendor-form').forEach(form => {
    form.style.display = 'none';
  });
  
  // Set form values
  document.getElementById('edit-business-' + vendorId).value = businessName;
  document.getElementById('edit-location-' + vendorId).value = location;
  document.getElementById('edit-phone-' + vendorId).value = contactPhone;
  
  // Show this edit form
  document.getElementById('edit-form-' + vendorId).style.display = 'table-row';
}

function hideEditForm(vendorId) {
  document.getElementById('edit-form-' + vendorId).style.display = 'none';
}
</script>

>>>>>>> fbe2f2352f51f03e7ea1f2afe40b2cc8d8bb19ff
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';