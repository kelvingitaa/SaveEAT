<?php
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\VendorController;
use App\Controllers\ConsumerController;

/* @var $router Router */
$router->get('/', [ConsumerController::class, 'index']);

// Auth
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users/create', [AdminController::class, 'createUser']);
$router->post('/admin/users/update', [AdminController::class, 'updateUser']);
$router->post('/admin/users/toggle-status', [AdminController::class, 'toggleUserStatus']);
$router->post('/admin/users/delete', [AdminController::class, 'deleteUser']);
$router->get('/admin/vendors', [AdminController::class, 'vendors']);
$router->post('/admin/vendors/approve', [AdminController::class, 'approveVendor']);
$router->get('/admin/categories', [AdminController::class, 'categories']);
$router->post('/admin/categories', [AdminController::class, 'categoryStore']);

// Vendor
$router->get('/vendor', [VendorController::class, 'index']);
$router->get('/vendor/items', [VendorController::class, 'items']);
$router->post('/vendor/items', [VendorController::class, 'itemStore']);
$router->post('/admin/vendors/create', [AdminController::class, 'createVendor']);
$router->post('/admin/vendors/update', [AdminController::class, 'updateVendor']);
$router->post('/admin/vendors/toggle-status', [AdminController::class, 'toggleVendorStatus']);
$router->post('/admin/vendors/delete', [AdminController::class, 'deleteVendor']);

// Consumer
$router->get('/consumer', [ConsumerController::class, 'index']);
$router->get('/consumer/cart', [ConsumerController::class, 'cart']);
$router->post('/consumer/cart/add', [ConsumerController::class, 'cartAdd']);
$router->post('/consumer/checkout', [ConsumerController::class, 'checkout']);
$router->get('/consumer/orders', [ConsumerController::class, 'orders']);

// Categories
$router->get('/admin/categories', [AdminController::class, 'categories']);
$router->post('/admin/categories', [AdminController::class, 'categoryStore']);
$router->post('/admin/categories/update', [AdminController::class, 'categoryUpdate']);
$router->post('/admin/categories/delete', [AdminController::class, 'categoryDelete']);