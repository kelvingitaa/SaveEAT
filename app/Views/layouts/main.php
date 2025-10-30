<?php
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light" style="background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-white" href="<?= BASE_URL ?>/consumer" style="font-size:1.5rem;">SaveEAT</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer">Home</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/consumer/cart">Cart</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/vendor">Vendor</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin">Admin</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (\App\Core\Auth::check()): ?>
          <li class="nav-item"><span class="navbar-text text-white me-2">Hello, <?= htmlspecialchars(\App\Core\Auth::user()['name']) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light" href="<?= BASE_URL ?>/logout">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-light me-2" href="<?= BASE_URL ?>/login">Login</a></li>
          <li class="nav-item"><a class="btn btn-light" href="<?= BASE_URL ?>/register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin') !== false;
?>
<?php if ($isAdminPage): ?>
<div class="admin-dashboard-layout" style="display:flex;min-height:100vh;background:#f3f4f6;">
  <aside class="admin-sidepanel bg-white shadow-sm" style="width:260px;min-width:220px;max-width:260px;padding:2rem 1rem;display:flex;flex-direction:column;gap:2rem;">
    <div class="mb-4 text-center">
      <a href="<?= BASE_URL ?>/admin" class="fw-bold text-primary" style="font-size:1.7rem;text-decoration:none;"><i class="bi bi-speedometer2"></i> Admin</a>
    </div>
    <ul class="nav flex-column gap-2">
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin"><i class="bi bi-house"></i> Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people"></i> Users</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/vendors"><i class="bi bi-shop"></i> Vendors</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/categories"><i class="bi bi-tags"></i> Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/items"><i class="bi bi-basket"></i> Food Items</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders"><i class="bi bi-receipt"></i> Orders</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/logs"><i class="bi bi-journal-text"></i> Audit Logs</a></li>
    </ul>
    <div class="mt-auto text-center">
      <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-primary w-100"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </aside>
  <main class="admin-content-area" style="flex:1;padding:2.5rem 2rem;">
    <?php if (!empty($content)) echo $content; ?>
  </main>
</div>
<?php else: ?>
<div class="container py-4">
  <?php if (!empty($content)) echo $content; ?>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>