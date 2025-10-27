<?php
// app/Views/admin/orders.php
// Order Management Page (Admin)
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
          <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        </ul>
      </div>
    </nav>
    <!-- Main Content -->
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-receipt"></i> Order Management</h1>
      </div>
      <!-- Order Table -->
      <div class="card mb-4">
        <div class="card-header bg-white">
          <form class="form-inline d-flex gap-2">
            <input type="text" class="form-control" placeholder="Search orders...">
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
                  <th scope="col">Vendor</th>
                  <th scope="col">Total</th>
                  <th scope="col">Status</th>
                  <th scope="col">Date</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Placeholder rows -->
                <tr>
                  <td>1</td>
                  <td>John Doe</td>
                  <td>Vendor A</td>
                  <td>$12.00</td>
                  <td><span class="badge bg-success">Completed</span></td>
                  <td>2025-10-27</td>
                  <td>
                    <button class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Jane Smith</td>
                  <td>Vendor B</td>
                  <td>$8.50</td>
                  <td><span class="badge bg-warning text-dark">Pending</span></td>
                  <td>2025-10-26</td>
                  <td>
                    <button class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </td>
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
