<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\FoodItem;
use App\Models\Category;
use App\Models\Order;
use App\Core\CSRF;

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
        if (!CSRF::check($_POST['_csrf'] ?? '')) { echo 'Invalid CSRF'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $cart = $_SESSION['cart'] ?? [];
        $cart[$id] = ($cart[$id] ?? 0) + $qty;
        $_SESSION['cart'] = $cart;
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

    public function checkout(): void
    {
        Auth::requireRole(['consumer']);
        if (!CSRF::check($_POST['_csrf'] ?? '')) { echo 'Invalid CSRF'; return; }
        $cart = $_SESSION['cart'] ?? [];
        if (!$cart) { $this->redirect('/consumer'); return; }
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
        $this->view('consumer/checkout_success', ['order_id' => $orderId, 'total' => $total]);
    }

    public function orders(): void
    {
        Auth::requireRole(['consumer']);
        $orders = (new Order())->byUser((int)Auth::id());
        $this->view('consumer/orders', ['orders' => $orders]);
    }
}
