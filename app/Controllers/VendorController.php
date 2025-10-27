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
        // Removed role restriction for direct dashboard access
        $vendor = (new Vendor())->byUser(1); // Use default user id for demo
        $this->view('vendor/dashboard', ['vendor' => $vendor]);
    }

    public function items(): void
    {
        Auth::requireRole(['vendor']);
        $vendor = (new Vendor())->byUser((int)Auth::id());
        if (!$vendor) {
            http_response_code(403);
            Session::flash('error', 'Vendor profile not found or not approved');
            $this->redirect('/vendor');
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
        $vendor = $vendorModel->findByUserId(Auth::userId());
        
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
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
}