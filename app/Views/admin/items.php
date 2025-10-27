<?php
// app/Views/admin/items.php
// Food Item Management Page (Admin)
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
          <li class="nav-item mb-2"><a class="nav-link active fw-bold" href="/admin/items"><i class="bi bi-basket"></i> Food Items</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
          <li class="nav-item mb-2"><a class="nav-link" href="/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
        </ul>
      </div>
    </nav>
    <!-- Main Content -->
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="bi bi-basket"></i> Food Item Management</h1>
        <button class="btn btn-primary"><i class="bi bi-plus"></i> Add Item</button>
      </div>
      <!-- Food Item Table -->
      <div class="card mb-4">
        <div class="card-header bg-white">
          <form class="form-inline d-flex gap-2">
            <input type="text" class="form-control" placeholder="Search items...">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
          </form>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Category</th>
                  <th scope="col">Vendor</th>
                  <th scope="col">Price</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Placeholder rows -->
                <tr>
                  <td>1</td>
                  <td>Cola</td>
                  <td>Beverages</td>
                  <td>Vendor A</td>
                  <td>$1.50</td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Chips</td>
                  <td>Snacks</td>
                  <td>Vendor B</td>
                  <td>$2.00</td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
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
