<?php
namespace App\Core;

$user = Auth::check() ? Auth::user() : null;
$userRole = $user['role'] ?? null;
$userName = $user['name'] ?? null;
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin') !== false;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar-brand { font-weight: 700; }
    .nav-link { transition: color 0.2s; }
    .nav-link:hover { color: #dbeafe !important; }
    .dropdown-menu { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .badge { font-size: 0.7em; }
    .sticky-top { z-index: 1020; }
    .admin-content-full { min-height: calc(100vh - 200px); background: #f8fafc; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light sticky-top" style="background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-white" href="<?= BASE_URL ?>" style="font-size:1.5rem;">
      <i class="bi bi-recycle me-2"></i>SaveEAT
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (Auth::check()): ?>
          
          <!-- Consumer Navigation -->
          <?php if ($userRole === 'consumer'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer"><i class="bi bi-house"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer/cart"><i class="bi bi-cart"></i> Cart</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer/orders"><i class="bi bi-receipt"></i> My Orders</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer"><i class="bi bi-shop"></i> Browse Food</a></li>
          <?php endif; ?>
          
          <!-- Vendor Navigation -->
          <?php if ($userRole === 'vendor'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/vendor"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/vendor/items"><i class="bi bi-basket"></i> Food Items</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/vendor/orders"><i class="bi bi-receipt"></i> Orders</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/vendor/donations"><i class="bi bi-heart"></i> Donations</a></li>
          <?php endif; ?>
          
          <!-- Driver Navigation -->
          <?php if ($userRole === 'driver'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/delivery/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/delivery/history"><i class="bi bi-clock-history"></i> Delivery History</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/delivery/settings"><i class="bi bi-gear"></i> Settings</a></li>
          <?php endif; ?>
          
          <!-- Shelter Navigation -->
          <?php if ($userRole === 'shelter'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/shelter/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/shelter/donations"><i class="bi bi-heart"></i> Donation Requests</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/shelter/history"><i class="bi bi-clock-history"></i> Donation History</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/shelter/settings"><i class="bi bi-gear"></i> Settings</a></li>
          <?php endif; ?>
          
          <!-- Admin Navigation -->
          <?php if ($userRole === 'admin'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> Users</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/shelters"><i class="bi bi-house-heart"></i> Shelters</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/approvals"><i class="bi bi-shield-check"></i> Approvals</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/items"><i class="bi bi-basket"></i> Food Items</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/donations"><i class="bi bi-heart"></i> Donations</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/reports"><i class="bi bi-graph-up"></i> Reports</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/logs"><i class="bi bi-clock-history"></i> Audit Logs</a></li>
          <?php endif; ?>
          
        <?php else: ?>
          <!-- Public Navigation -->
          <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/register"><i class="bi bi-person-plus"></i> Join Our Mission</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer"><i class="bi bi-shop"></i> Browse Food</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/donations"><i class="bi bi-heart"></i> Donations</a></li>
        <?php endif; ?>
      </ul>
      
      <ul class="navbar-nav ms-auto">
        <?php if (Auth::check()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle"></i> 
              <?= htmlspecialchars($userName) ?>
              <small class="badge bg-light text-dark ms-1"><?= ucfirst($userRole) ?></small>
            </a>
            <ul class="dropdown-menu">
              <li><span class="dropdown-item-text">
                <small>Signed in as</small><br>
                <strong><?= htmlspecialchars($user['email']) ?></strong>
              </span></li>
              <li><hr class="dropdown-divider"></li>
              
              <!-- Role-specific profile links -->
              <?php if ($userRole === 'consumer'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/consumer/orders"><i class="bi bi-receipt"></i> My Orders</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/consumer/cart"><i class="bi bi-cart"></i> Shopping Cart</a></li>
              <?php elseif ($userRole === 'vendor'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/vendor"><i class="bi bi-speedometer2"></i> Vendor Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/vendor/items"><i class="bi bi-basket"></i> Manage Items</a></li>
              <?php elseif ($userRole === 'driver'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/delivery/dashboard"><i class="bi bi-speedometer2"></i> Driver Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/delivery/settings"><i class="bi bi-gear"></i> Driver Settings</a></li>
              <?php elseif ($userRole === 'shelter'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/shelter/dashboard"><i class="bi bi-speedometer2"></i> Shelter Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/shelter/settings"><i class="bi bi-gear"></i> Shelter Settings</a></li>
              <?php elseif ($userRole === 'admin'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin"><i class="bi bi-speedometer2"></i> Admin Dashboard</a></li>
              <?php endif; ?>
              
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-light me-2" href="<?= BASE_URL ?>/login"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
          <li class="nav-item"><a class="btn btn-light" href="<?= BASE_URL ?>/register"><i class="bi bi-person-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Welcome Banner -->
<?php if (Auth::check()): ?>
  <?php 
  $roleColors = [
    'consumer' => 'success',
    'vendor' => 'warning',
    'driver' => 'info', 
    'shelter' => 'primary',
    'admin' => 'danger'
  ];
  
  $roleIcons = [
    'consumer' => 'bi-person',
    'vendor' => 'bi-shop',
    'driver' => 'bi-truck',
    'shelter' => 'bi-house-heart',
    'admin' => 'bi-shield-check'
  ];
  
  $roleMessages = [
    'consumer' => 'Browse delicious food and place orders',
    'vendor' => 'Manage your food items and orders',
    'driver' => 'Deliver orders and track your deliveries',
    'shelter' => 'Request and manage food donations',
    'admin' => 'Manage the platform and users'
  ];
  ?>
  
  <div class="alert alert-<?= $roleColors[$userRole] ?? 'secondary' ?> mb-0 rounded-0 border-0" style="border-bottom: 3px solid var(--bs-<?= $roleColors[$userRole] ?? 'secondary' ?>);">
    <div class="container">
      <div class="d-flex align-items-center">
        <i class="bi <?= $roleIcons[$userRole] ?? 'bi-person' ?> me-2 fs-4"></i>
        <div>
          <strong>Welcome back, <?= htmlspecialchars($userName) ?>!</strong> 
          <span class="badge bg-<?= $roleColors[$userRole] ?? 'secondary' ?> ms-2"><?= ucfirst($userRole) ?></span>
          <div class="text-muted small"><?= $roleMessages[$userRole] ?? 'Welcome to SaveEAT' ?></div>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <!-- Public welcome banner for guests -->
  <div class="alert alert-info mb-0 rounded-0 border-0" style="border-bottom: 3px solid var(--bs-info);">
    <div class="container">
      <div class="d-flex align-items-center">
        <i class="bi bi-recycle me-2 fs-4"></i>
        <div>
          <strong>Welcome to SaveEAT!</strong> 
          <span class="badge bg-info ms-2">Join Our Mission</span>
          <div class="text-muted small">Reduce food waste by connecting vendors, consumers, and shelters</div>
        </div>
        <div class="ms-auto">
          <a href="<?= BASE_URL ?>/register" class="btn btn-sm btn-outline-info">Get Started</a>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- CONTENT AREA - NO SIDEBAR FOR ADMIN -->
<?php if ($isAdminPage): ?>
  <!-- Admin pages use full-width layout without sidebar -->
  <div class="admin-content-full">
    <div class="container-fluid py-4">
      <?php if (!empty($content)) echo $content; ?>
    </div>
  </div>
<?php else: ?>
  <!-- Regular page content -->
  <div class="container py-4">
    <?php if (!empty($content)) echo $content; ?>
  </div>
<?php endif; ?>

<footer class="text-light py-4 mt-5" style="background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h5><i class="bi bi-recycle"></i> SaveEAT</h5>
        <p>Reducing food waste, one meal at a time. Connecting vendors with consumers and shelters.</p>
      </div>
      <div class="col-md-3">
        <h6>Quick Links</h6>
        <ul class="list-unstyled">
          <?php if (Auth::check()): ?>
            <?php if ($userRole === 'consumer'): ?>
              <li><a href="<?= BASE_URL ?>/consumer" class="text-light">Browse Food</a></li>
            <?php endif; ?>
            <li><a href="<?= BASE_URL ?>/donations" class="text-light">Food Donations</a></li>
          <?php else: ?>
            <li><a href="<?= BASE_URL ?>/consumer" class="text-light">Browse Food</a></li>
            <li><a href="<?= BASE_URL ?>/donations" class="text-light">Food Donations</a></li>
          <?php endif; ?>
          <li><a href="<?= BASE_URL ?>/register" class="text-light">Join Us</a></li>
        </ul>
      </div>
      <div class="col-md-3">
        <h6>Account</h6>
        <ul class="list-unstyled">
          <?php if (Auth::check()): ?>
            <li><a href="<?= BASE_URL ?>/logout" class="text-light">Logout</a></li>
          <?php else: ?>
            <li><a href="<?= BASE_URL ?>/login" class="text-light">Login</a></li>
            <li><a href="<?= BASE_URL ?>/register" class="text-light">Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <hr class="bg-light">
    <div class="text-center">
      <small>&copy; 2025 SaveEAT. All rights reserved. Fighting food waste together.</small>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>