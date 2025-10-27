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


// Shelter Routes
$router->get('/shelter/register', [ShelterController::class, 'register']);
$router->post('/shelter/register', [ShelterController::class, 'store']);
$router->get('/shelter/dashboard', [ShelterController::class, 'dashboard']);

// Admin verification routes
$router->get('/admin/shelters', [AdminController::class, 'shelters']);
$router->post('/admin/shelters/approve', [AdminController::class, 'approveShelter']);
$router->get('/admin/verifications', [AdminController::class, 'vendorVerifications']);
$router->post('/admin/verifications/approve', [AdminController::class, 'approveVendorVerification']);
$router->post('/admin/verifications/reject', [AdminController::class, 'rejectVendorVerification']);


// Payment Routes
$router->get('/payment/process/{id}', [PaymentController::class, 'process']);
$router->post('/payment/initiate', [PaymentController::class, 'initiate']);
$router->get('/payment/success/{id}', [PaymentController::class, 'success']);
$router->get('/payment/failed/{id}', [PaymentController::class, 'failed']);

// Delivery Routes
$router->get('/delivery/dashboard', [DeliveryController::class, 'dashboard']);
$router->post('/delivery/update-status', [DeliveryController::class, 'updateStatus']);
$router->post('/delivery/assign', [DeliveryController::class, 'assignDelivery']);
$router->post('/delivery/update-delivery-status', [DeliveryController::class, 'updateDeliveryStatus']);
$router->get('/delivery/track/{id}', [DeliveryController::class, 'track']);

// Verification Routes
$router->get('/verification/upload-license', [VerificationController::class, 'uploadLicense']);
$router->post('/verification/process-license', [VerificationController::class, 'processLicense']);
$router->get('/verification/status', [VerificationController::class, 'status']);

// Reports Route
$router->get('/admin/reports', [AdminController::class, 'reports']);

// Cron job route (for manual triggering during development)
$router->get('/cron/update-food-status', function() {
    require __DIR__ . '/../scripts/update_food_status.php';
});