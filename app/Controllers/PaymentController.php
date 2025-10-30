<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Payment;
use App\Models\Order;
use App\Core\CSRF;

class PaymentController extends Controller
{
    public function process(int $orderId): void
    {
        Auth::requireRole(['consumer']);
        
        $orderModel = new Order();
        $order = $orderModel->find($orderId);
        
        if (!$order || $order['user_id'] !== Auth::userId()) {
            Session::flash('error', 'Order not found');
            $this->redirect('/consumer/orders');
        }
        
        if ($order['status'] !== 'pending') {
            Session::flash('error', 'Order already processed');
            $this->redirect('/consumer/orders');
        }
        
        $this->view('payment/process', [
            'order' => $order
        ]);
    }

    public function initiate(): void
    {
        Auth::requireRole(['consumer']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/consumer/orders');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/consumer/orders');
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'mobile_money';
        $phoneNumber = $_POST['phone_number'] ?? '';
        
        $orderModel = new Order();
        $order = $orderModel->find($orderId);
        
        if (!$order || $order['user_id'] !== Auth::userId()) {
            Session::flash('error', 'Order not found');
            $this->redirect('/consumer/orders');
        }
        
        try {
            $paymentModel = new Payment();
            
            // Create payment record
            $paymentId = $paymentModel->createPayment([
                'order_id' => $orderId,
                'amount' => $order['total_price'],
                'payment_method' => $paymentMethod,
                'transaction_id' => 'TXN_' . time() . '_' . $orderId,
                'payment_status' => 'pending'
            ]);
            
            // Simulate mobile money payment (replace with actual M-Pesa API)
            $paymentSuccess = $this->simulateMobileMoneyPayment($phoneNumber, $order['total_price']);
            
            if ($paymentSuccess) {
                // Update payment status
                $paymentModel->updatePaymentStatus($paymentId, 'completed', 'MPESA_' . time());
                
                // Update order status
                $orderModel->updateStatus($orderId, 'paid');
                
                Session::flash('success', 'Payment completed successfully! Your order is being processed.');
                $this->redirect('/consumer/orders');
            } else {
                $paymentModel->updatePaymentStatus($paymentId, 'failed');
                Session::flash('error', 'Payment failed. Please try again.');
                $this->redirect("/payment/process/{$orderId}");
            }
            
        } catch (\Throwable $e) {
            Session::flash('error', 'Payment processing failed: ' . $e->getMessage());
            $this->redirect("/payment/process/{$orderId}");
        }
    }

    private function simulateMobileMoneyPayment(string $phoneNumber, float $amount): bool
    {
        // Simulate API call to mobile money provider (M-Pesa, Airtel Money, etc.)
        // In real implementation, integrate with actual payment gateway
        sleep(2); // Simulate API delay
        
        // For demo purposes, assume payment succeeds for valid phone numbers
        return !empty($phoneNumber) && strlen($phoneNumber) >= 10;
    }

    public function success(int $orderId): void
    {
        Auth::requireRole(['consumer']);
        $this->view('payment/success', ['orderId' => $orderId]);
    }

    public function failed(int $orderId): void
    {
        Auth::requireRole(['consumer']);
        $this->view('payment/failed', ['orderId' => $orderId]);
    }
}