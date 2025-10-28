<?php
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\VendorController;
use App\Controllers\ConsumerController;
use App\Controllers\ShelterController;
use App\Controllers\PaymentController;
use App\Controllers\DeliveryController;
use App\Controllers\VerificationController;
use App\Controllers\DonationController;

/* @var $router Router */

// Home
$router->get('/', [ConsumerController::class, 'index']);

// Auth Routes - UPDATED
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegisterSelect']);
$router->get('/register/consumer', [AuthController::class, 'showConsumerRegistration']);
$router->post('/register/consumer', [AuthController::class, 'registerConsumer']);
$router->get('/register/vendor', [AuthController::class, 'showVendorRegistration']);
$router->post('/register/vendor', [AuthController::class, 'registerVendor']);
$router->get('/register/shelter', [AuthController::class, 'showShelterRegistration']);
$router->post('/register/shelter', [AuthController::class, 'registerShelter']);
$router->get('/register/driver', [AuthController::class, 'showDriverRegistration']);
$router->post('/register/driver', [AuthController::class, 'registerDriver']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin Routes
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
$router->post('/admin/categories/update', [AdminController::class, 'categoryUpdate']);
$router->post('/admin/categories/delete', [AdminController::class, 'categoryDelete']);
$router->get('/admin/shelters', [AdminController::class, 'shelters']);
$router->post('/admin/shelters/approve', [AdminController::class, 'approveShelter']);
$router->get('/admin/verifications', [AdminController::class, 'vendorVerifications']);
$router->post('/admin/verifications/approve', [AdminController::class, 'approveVendorVerification']);
$router->post('/admin/verifications/reject', [AdminController::class, 'rejectVendorVerification']);
$router->get('/admin/reports', [AdminController::class, 'reports']);

// Vendor Routes
$router->get('/vendor', [VendorController::class, 'index']);
$router->get('/vendor/items', [VendorController::class, 'items']);
$router->post('/vendor/items', [VendorController::class, 'itemStore']);

// Consumer Routes
$router->get('/consumer', [ConsumerController::class, 'index']);
$router->get('/consumer/cart', [ConsumerController::class, 'cart']);
$router->post('/consumer/cart/add', [ConsumerController::class, 'cartAdd']);
$router->post('/consumer/checkout', [ConsumerController::class, 'checkout']);
$router->get('/consumer/orders', [ConsumerController::class, 'orders']);
$router->post('/consumer/cart/update', [ConsumerController::class, 'cartUpdate']);
$router->post('/consumer/cart/remove', [ConsumerController::class, 'cartRemove']);

// Shelter Routes
$router->get('/shelter/register', [ShelterController::class, 'register']);
$router->post('/shelter/register', [ShelterController::class, 'store']);
$router->get('/shelter/dashboard', [ShelterController::class, 'dashboard']);
$router->get('/shelter/donations', [ShelterController::class, 'donationRequests']);
$router->get('/shelter/history', [ShelterController::class, 'donationHistory']);
$router->get('/shelter/settings', [ShelterController::class, 'settings']);
$router->post('/shelter/donations/request', [ShelterController::class, 'requestDonation']);
$router->post('/shelter/settings/update', [ShelterController::class, 'updateSettings']);

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
$router->get('/delivery/history', [DeliveryController::class, 'history']);
$router->get('/delivery/settings', [DeliveryController::class, 'settings']);
$router->post('/delivery/update-profile', [DeliveryController::class, 'updateProfile']);
$router->post('/delivery/change-password', [DeliveryController::class, 'changePassword']);

// Verification Routes
$router->get('/verification/upload-license', [VerificationController::class, 'uploadLicense']);
$router->post('/verification/process-license', [VerificationController::class, 'processLicense']);
$router->get('/verification/status', [VerificationController::class, 'status']);

// Cron job route
$router->get('/cron/update-food-status', function() {
    require __DIR__ . '/../scripts/update_food_status.php';
});