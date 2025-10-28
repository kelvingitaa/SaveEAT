<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Uploader;
use App\Core\CSRF;
use App\Core\Session;
use App\Models\Vendor;
use App\Models\FoodItem;
use App\Models\Category;

class VendorController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId((int)Auth::id());
        
        if (!$vendor) {
            http_response_code(403);
            Session::flash('error', 'Vendor profile not found');
            $this->view('vendor/dashboard', ['vendor' => null]);
            return;
        }
        
        if (!$vendor['approved']) {
            Session::flash('error', 'Your account is pending admin approval. You will be able to list items after approval.');
            $this->view('vendor/dashboard', ['vendor' => $vendor]);
            return;
        }
        
        // Vendor is approved - show full dashboard
        $this->view('vendor/dashboard', ['vendor' => $vendor]);
    }

    public function items(): void
    {
        Auth::requireRole(['vendor']);
        $vendor = (new Vendor())->findByUserId((int)Auth::id());
        if (!$vendor) {
            http_response_code(403);
            Session::flash('error', 'Vendor profile not found');
            $this->redirect('/vendor');
            return;
        }
        
        if (!$vendor['approved']) {
            Session::flash('error', 'Your account is pending admin approval. You will be able to list items after approval.');
            $this->redirect('/vendor');
            return;
        }
        
        $db = (new FoodItem())->getDb();
        $stmt = $db->prepare('SELECT fi.*, c.name AS category_name FROM food_items fi LEFT JOIN categories c ON c.id = fi.category_id WHERE fi.vendor_id = :vid ORDER BY fi.created_at DESC');
        $stmt->execute(['vid' => $vendor['id']]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $cats = (new Category())->all();
        $this->view('vendor/items', ['items' => $items, 'categories' => $cats, 'vendor' => $vendor]);
    }

    public function itemStore(): void
    {
        Auth::requireRole(['vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/vendor/items');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/vendor/items');
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId((int)Auth::id());
        
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
            return;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $discountPercent = max(0, min(90, (int)($_POST['discount_percent'] ?? 0)));
        $expiryDate = $_POST['expiry_date'] ?? '';
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        
        // Validation
        if (empty($name) || $price <= 0 || empty($expiryDate) || $stock <= 0 || $categoryId <= 0) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/vendor/items');
        }
        
        // Food Safety: Check if expiry is at least 24 hours from now
        $foodModel = new FoodItem();
        
        if (!$foodModel->enforce24HourRule(['expiry_date' => $expiryDate])) {
            Session::flash('error', 'Food items must be safe to eat for at least 24 hours. Please set an expiry date at least 24 hours from now.');
            $this->redirect('/vendor/items');
        }
        
        // Handle image upload
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $image = Uploader::image($_FILES['image']);
            if ($image === null) {
                Session::flash('error', 'Image upload failed. Ensure file type and size are valid.');
                $this->redirect('/vendor/items');
            }
            $imagePath = $image;
        }
        
        try {
            $db = $foodModel->getDb();
            $stmt = $db->prepare("
                INSERT INTO food_items (vendor_id, category_id, name, description, price, 
                                      discount_percent, expiry_date, stock, image_path, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ");
            $stmt->execute([
                $vendor['id'], 
                $categoryId, 
                $name, 
                $description, 
                $price, 
                $discountPercent, 
                $expiryDate, 
                $stock,
                $imagePath
            ]);
            
            Session::flash('success', 'Food item added successfully!');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to add food item: ' . $e->getMessage());
        }
        
        $this->redirect('/vendor/items');
    }

    public function itemUpdate(): void
    {
        Auth::requireRole(['vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/vendor/items');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/vendor/items');
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId((int)Auth::id());
        
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
            return;
        }
        
        $itemId = (int)($_POST['item_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $discountPercent = max(0, min(90, (int)($_POST['discount_percent'] ?? 0)));
        $expiryDate = $_POST['expiry_date'] ?? '';
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        
        // Validation
        if ($itemId <= 0 || empty($name) || $price <= 0 || empty($expiryDate) || $stock <= 0 || $categoryId <= 0) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/vendor/items');
        }
        
        // Food Safety: Check if expiry is at least 24 hours from now
        $foodModel = new FoodItem();
        
        if (!$foodModel->enforce24HourRule(['expiry_date' => $expiryDate])) {
            Session::flash('error', 'Food items must be safe to eat for at least 24 hours. Please set an expiry date at least 24 hours from now.');
            $this->redirect('/vendor/items');
        }
        
        // Handle image upload
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $image = Uploader::image($_FILES['image']);
            if ($image === null) {
                Session::flash('error', 'Image upload failed. Ensure file type and size are valid.');
                $this->redirect('/vendor/items');
            }
            $imagePath = $image;
        }
        
        try {
            $db = $foodModel->getDb();
            
            if ($imagePath) {
                $stmt = $db->prepare("
                    UPDATE food_items 
                    SET name = ?, description = ?, price = ?, discount_percent = ?, 
                        expiry_date = ?, stock = ?, category_id = ?, image_path = ?, updated_at = NOW()
                    WHERE id = ? AND vendor_id = ?
                ");
                $stmt->execute([
                    $name, $description, $price, $discountPercent, 
                    $expiryDate, $stock, $categoryId, $imagePath,
                    $itemId, $vendor['id']
                ]);
            } else {
                $stmt = $db->prepare("
                    UPDATE food_items 
                    SET name = ?, description = ?, price = ?, discount_percent = ?, 
                        expiry_date = ?, stock = ?, category_id = ?, updated_at = NOW()
                    WHERE id = ? AND vendor_id = ?
                ");
                $stmt->execute([
                    $name, $description, $price, $discountPercent, 
                    $expiryDate, $stock, $categoryId,
                    $itemId, $vendor['id']
                ]);
            }
            
            Session::flash('success', 'Food item updated successfully!');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update food item: ' . $e->getMessage());
        }
        
        $this->redirect('/vendor/items');
    }

    public function itemDelete(): void
    {
        Auth::requireRole(['vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/vendor/items');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/vendor/items');
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId((int)Auth::id());
        
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
            return;
        }
        
        $itemId = (int)($_POST['item_id'] ?? 0);
        
        if ($itemId <= 0) {
            Session::flash('error', 'Invalid item ID');
            $this->redirect('/vendor/items');
        }
        
        try {
            $foodModel = new FoodItem();
            $db = $foodModel->getDb();
            $stmt = $db->prepare("DELETE FROM food_items WHERE id = ? AND vendor_id = ?");
            $stmt->execute([$itemId, $vendor['id']]);
            
            Session::flash('success', 'Food item deleted successfully!');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to delete food item: ' . $e->getMessage());
        }
        
        $this->redirect('/vendor/items');
    }

    public function orders(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId((int)Auth::id());
        
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
            return;
        }
        
        // Get vendor's orders
        $db = (new FoodItem())->getDb();
        $stmt = $db->prepare("
            SELECT o.*, u.name as customer_name, u.phone as customer_phone,
                   GROUP_CONCAT(fi.name SEPARATOR ', ') as items
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN food_items fi ON oi.food_item_id = fi.id
            JOIN users u ON o.user_id = u.id
            WHERE fi.vendor_id = :vendor_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute(['vendor_id' => $vendor['id']]);
        $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->view('vendor/orders', ['orders' => $orders, 'vendor' => $vendor]);
    }

    public function updateOrderStatus(): void
    {
        Auth::requireRole(['vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/vendor/orders');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/vendor/orders');
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId((int)Auth::id());
        
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
            return;
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        $allowedStatuses = ['preparing', 'ready', 'completed'];
        if ($orderId <= 0 || !in_array($status, $allowedStatuses)) {
            Session::flash('error', 'Invalid order or status');
            $this->redirect('/vendor/orders');
        }
        
        try {
            $db = (new FoodItem())->getDb();
            
            // Verify the order belongs to this vendor
            $verifyStmt = $db->prepare("
                SELECT o.id FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN food_items fi ON oi.food_item_id = fi.id
                WHERE o.id = ? AND fi.vendor_id = ?
                LIMIT 1
            ");
            $verifyStmt->execute([$orderId, $vendor['id']]);
            
            if ($verifyStmt->fetch()) {
                $updateStmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$status, $orderId]);
                Session::flash('success', 'Order status updated successfully!');
            } else {
                Session::flash('error', 'Order not found or access denied');
            }
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update order status: ' . $e->getMessage());
        }
        
        $this->redirect('/vendor/orders');
    }
}