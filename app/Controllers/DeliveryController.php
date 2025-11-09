<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\DeliveryDriver;
use App\Core\CSRF;

class DeliveryController extends Controller
{
    public function dashboard(): void
{
    Auth::requireRole(['driver']);
    
    $driverModel = new DeliveryDriver();
    $deliveryModel = new Delivery();
    
    $driver = $driverModel->findByUserId(Auth::id());
    $assignedDeliveries = $deliveryModel->getActiveDeliveriesByDriver($driver['id']);
    $availableDeliveries = $deliveryModel->getAvailableDeliveries();
    
    $this->view('delivery/dashboard', [
        'driver' => $driver,
        'assignedDeliveries' => $assignedDeliveries,
        'availableDeliveries' => $availableDeliveries
    ]);
}

    public function updateStatus(): void
    {
        Auth::requireRole(['driver']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/delivery/dashboard');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/delivery/dashboard');
        }
        
        $driverId = (int)($_POST['driver_id'] ?? 0);
        $status = $_POST['status'] ?? 'offline';
        
        try {
            $driverModel = new DeliveryDriver();
            $driverModel->updateStatus($driverId, $status);
            
            Session::flash('success', 'Status updated successfully');
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to update status: ' . $e->getMessage());
        }
        
        $this->redirect('/delivery/dashboard');
    }

    public function assignDelivery(): void
    {
        Auth::requireRole(['admin', 'vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/orders');
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $driverId = (int)($_POST['driver_id'] ?? 0);
        
        try {
            $deliveryModel = new Delivery();
            $success = $deliveryModel->assignDriver($orderId, $driverId);
            
            if ($success) {
                Session::flash('success', 'Delivery assigned successfully');
            } else {
                Session::flash('error', 'Failed to assign delivery');
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to assign delivery: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/orders');
    }

    public function updateDeliveryStatus(): void
    {
        Auth::requireRole(['driver']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/delivery/dashboard');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/delivery/dashboard');
        }
        
        $deliveryId = (int)($_POST['delivery_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        
        try {
            $deliveryModel = new Delivery();
            $success = $deliveryModel->updateStatus($deliveryId, $status);
            
            if ($success) {
                Session::flash('success', 'Delivery status updated');
            } else {
                Session::flash('error', 'Failed to update delivery status');
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to update delivery status: ' . $e->getMessage());
        }
        
        $this->redirect('/delivery/dashboard');
    }

    public function track(int $orderId): void
    {
        Auth::requireRole(['consumer', 'admin', 'vendor']);
        
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->findByOrderId($orderId);
        
        $this->view('delivery/tracking', [
            'delivery' => $delivery,
            'orderId' => $orderId
        ]);
    }

    public function history(): void
    {
        Auth::requireRole(['driver']);
        
        $driverModel = new DeliveryDriver();
        $deliveryModel = new Delivery();
        
        $driver = $driverModel->findByUserId(Auth::id());
        
        if (!$driver) {
            Session::flash('error', 'Driver profile not found');
            $this->redirect('/delivery/dashboard');
            return;
        }
        
        $completedDeliveries = $deliveryModel->getCompletedDeliveriesByDriver($driver['id']);
        
        $this->view('delivery/history', [
            'driver' => $driver,
            'completedDeliveries' => $completedDeliveries
        ]);
    }

    public function settings(): void
    {
        Auth::requireRole(['driver']);
        
        $driverModel = new DeliveryDriver();
        $userModel = new \App\Models\User();
        
        $driver = $driverModel->findByUserId(Auth::id());
        $user = $userModel->findByEmail(Auth::user()['email']);
        
        // Merge user and driver data for the view
        $driverData = array_merge($user, $driver);
        
        $this->view('delivery/settings', [
            'driver' => $driverData
        ]);
    }

    public function updateProfile(): void
    {
        Auth::requireRole(['driver']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/delivery/settings');
            return;
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/delivery/settings');
            return;
        }
        
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $vehicleType = $_POST['vehicle_type'] ?? '';
        $licensePlate = trim($_POST['license_plate'] ?? '');
        
        // Validation
        if (empty($name) || empty($phone) || empty($vehicleType)) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/delivery/settings');
            return;
        }
        
        try {
            $driverModel = new DeliveryDriver();
            $userModel = new \App\Models\User();
            
            $driver = $driverModel->findByUserId(Auth::id());
            
            if (!$driver) {
                Session::flash('error', 'Driver profile not found');
                $this->redirect('/delivery/settings');
                return;
            }
            
            // Update user information
            $userModel->update(Auth::id(), [
                'name' => $name,
                'phone' => $phone
            ]);
            
            // Update driver information
            $driverModel->update($driver['id'], [
                'vehicle_type' => $vehicleType,
                'license_plate' => $licensePlate
            ]);
            
            // Update session with new user data
            $updatedUser = $userModel->findByEmail(Auth::user()['email']);
            Auth::attempt($updatedUser);
            
            Session::flash('success', 'Profile updated successfully');
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to update profile: ' . $e->getMessage());
        }
        
        $this->redirect('/delivery/settings');
    }

    public function changePassword(): void
    {
        Auth::requireRole(['driver']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/delivery/settings');
            return;
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/delivery/settings');
            return;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Session::flash('error', 'All password fields are required');
            $this->redirect('/delivery/settings');
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New passwords do not match');
            $this->redirect('/delivery/settings');
            return;
        }
        
        if (strlen($newPassword) < 8) {
            Session::flash('error', 'New password must be at least 8 characters long');
            $this->redirect('/delivery/settings');
            return;
        }
        
        try {
            $userModel = new \App\Models\User();
            $user = $userModel->findByEmail(Auth::user()['email']);
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                Session::flash('error', 'Current password is incorrect');
                $this->redirect('/delivery/settings');
                return;
            }
            
            // Update password
            $userModel->update(Auth::id(), [
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);
            
            Session::flash('success', 'Password changed successfully');
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to change password: ' . $e->getMessage());
        }
        
        $this->redirect('/delivery/settings');
    }
}