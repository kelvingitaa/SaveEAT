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
        
        $driver = $driverModel->findByUserId(Auth::userId());
        $assignedDeliveries = $deliveryModel->getDeliveriesByDriver($driver['id']);
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
}