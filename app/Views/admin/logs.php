<?php
// app/Views/admin/logs.php
// Audit Log Management Page (Admin)
?>
<div class="container-fluid">
  <div class="row">
    <!-- Sidepanel -->
    <nav class="col-md-2 d-none d-md-block bg-light sidebar py-4" style="min-height:100vh;">
      <div class="sidebar-sticky">
        <ul class="nav flex-column">
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/dashboard"><i class="bi bi-house"></i> Dashboard</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/users"><i class="bi bi-people"></i> Users</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/items"><i class="bi bi-basket"></i> Food Items</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
          <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        </ul>
      </div>
    </nav>
    <!-- Main Content -->
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-journal-text"></i> Audit Log Management</h1>
        <button class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export Logs</button>
      </div>
      <!-- Audit Log Table -->
      <div class="card mb-4">
        <div class="card-header bg-white">
          <form class="form-inline d-flex gap-2">
            <input type="text" class="form-control" placeholder="Search logs...">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
          </form>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">User</th>
                  <th scope="col">Action</th>
                  <th scope="col">Details</th>
                  <th scope="col">Timestamp</th>
                </tr>
              </thead>
              <tbody>
                <!-- Placeholder rows -->
                <tr>
                  <td>1</td>
                  <td>admin</td>
                  <td>Delete Item</td>
                  <td>Deleted item #5 (Chips)</td>
                  <td>2025-10-27 10:15:00</td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>vendorA</td>
                  <td>Add Category</td>
                  <td>Added category "Beverages"</td>
                  <td>2025-10-27 09:45:00</td>
                </tr>
                <!-- ...more rows... -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
<?php // ...existing code... ?>
