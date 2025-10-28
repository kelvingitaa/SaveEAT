<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session; // ADD THIS IMPORT
use App\Models\FoodItem;
use App\Models\Category;
use App\Models\Order;

class ConsumerController extends Controller
{
    public function index(): void
    {
        // Removed role restriction for public landing page
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'q' => $_GET['q'] ?? null,
        ];
        $items = (new FoodItem())->browse($filters, 12, 0);
        $cats = (new Category())->all();
        $this->view('consumer/browse', ['items' => $items, 'categories' => $cats]);
    }

    public function cartAdd(): void
    {
        Auth::requireRole(['consumer']);
        if (!CSRF::check($_POST['_csrf'] ?? '')) { 
            Session::flash('error', 'Invalid CSRF token'); // ADD SESSION FLASH
            $this->redirect('/consumer/cart');
            return; 
        }
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $cart = $_SESSION['cart'] ?? [];
        $cart[$id] = ($cart[$id] ?? 0) + $qty;
        $_SESSION['cart'] = $cart;
        Session::flash('success', 'Item added to cart'); // ADD SUCCESS MESSAGE
        $this->redirect('/consumer/cart');
    }

    public function cart(): void
    {
        Auth::requireRole(['consumer']);
        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        $total = 0.0;
        $fm = new FoodItem();
        foreach ($cart as $id => $qty) {
            $prod = $fm->find((int)$id);
            if ($prod) {
                $price = (float)$prod['price'];
                $discount = (int)$prod['discount_percent'];
                $final = round($price * (1 - $discount / 100), 2);
                $line = $final * $qty;
                $items[] = ['id' => $prod['id'], 'name' => $prod['name'], 'qty' => $qty, 'price' => $price, 'discount_percent' => $discount, 'line_total' => $line];
                $total += $line;
            }
        }
        $this->view('consumer/cart', ['items' => $items, 'total' => $total]);
    }

    public function cartUpdate(): void
    {
        Auth::requireRole(['consumer']);
        if (!CSRF::check($_POST['_csrf'] ?? '')) { 
            Session::flash('error', 'Invalid CSRF token'); 
            $this->redirect('/consumer/cart');
            return;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, min(10, (int)($_POST['qty'] ?? 1))); // Limit to 10 max
        
        $cart = $_SESSION['cart'] ?? [];
        if (isset($cart[$id])) {
            $cart[$id] = $qty;
            $_SESSION['cart'] = $cart;
            Session::flash('success', 'Cart updated successfully');
        }
        
        $this->redirect('/consumer/cart');
    }

    public function cartRemove(): void
    {
        Auth::requireRole(['consumer']);
        if (!CSRF::check($_POST['_csrf'] ?? '')) { 
            Session::flash('error', 'Invalid CSRF token'); 
            $this->redirect('/consumer/cart');
            return;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        $cart = $_SESSION['cart'] ?? [];
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $_SESSION['cart'] = $cart;
            Session::flash('success', 'Item removed from cart');
        }
        
        $this->redirect('/consumer/cart');
    }

    public function checkout(): void
    {
        Auth::requireRole(['consumer']);
        if (!CSRF::check($_POST['_csrf'] ?? '')) { 
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/consumer/cart');
            return; 
        }
        $cart = $_SESSION['cart'] ?? [];
        if (!$cart) { 
            Session::flash('error', 'Your cart is empty');
            $this->redirect('/consumer'); 
            return; 
        }
        $items = [];
        $total = 0.0;
        $fm = new FoodItem();
        foreach ($cart as $id => $qty) {
            $prod = $fm->find((int)$id);
            if ($prod) {
                $price = (float)$prod['price'];
                $discount = (int)$prod['discount_percent'];
                $final = round($price * (1 - $discount / 100), 2);
                $line = $final * $qty;
                $items[] = ['id' => $prod['id'], 'qty' => $qty, 'price' => $price, 'discount_percent' => $discount, 'line_total' => $line];
                $total += $line;
            }
        }
        $orderId = (new Order())->createOrder((int)\App\Core\Auth::id(), $items, $total);
        unset($_SESSION['cart']);
        Session::flash('success', 'Order placed successfully!'); // ADD SUCCESS MESSAGE
        $this->view('consumer/checkout_success', ['order_id' => $orderId, 'total' => $total]);
    }

    public function orders(): void
    {
        Auth::requireRole(['consumer']);
        $orders = (new Order())->byUser((int)Auth::id());
        $this->view('consumer/orders', ['orders' => $orders]);
    }

    public function orderDetails($orderId): void
{
    Auth::requireRole(['consumer']);
    
    // Get order details
    $order = (new Order())->find((int)$orderId);
    
    // Verify the order belongs to the current user
    if (!$order || $order['user_id'] != Auth::id()) {
        Session::flash('error', 'Order not found');
        $this->redirect('/consumer/orders');
        return;
    }
    
    // Get order items 
    $orderItems = (new Order())->getOrderItems((int)$orderId);
    
    $this->view('consumer/order-details', [
        'order' => $order,
        'orderItems' => $orderItems
    ]);
}
}