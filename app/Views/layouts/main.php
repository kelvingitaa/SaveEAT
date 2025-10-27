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
<div class="container py-4">
  <?php if (!empty($content)) echo $content; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>