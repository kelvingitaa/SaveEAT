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
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
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
        $vendor = (new Vendor())->byUser((int)Auth::id());
        if (!$vendor || !$vendor['approved']) {
            http_response_code(403);
            Session::flash('error', 'Your vendor account is not approved yet');
            $this->redirect('/vendor');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: 0;
        $price = (float)($_POST['price'] ?? 0);
        $discount = max(0, min(90, (int)($_POST['discount_percent'] ?? 0)));
        $expiry = $_POST['expiry_date'] ?? date('Y-m-d');
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($name === '' || $categoryId <= 0 || $price <= 0 || !$expiry) {
            Session::flash('error', 'Please fill all required fields with valid values');
            $this->redirect('/vendor/items');
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $img = Uploader::image($_FILES['image']);
            if ($img === null) {
                Session::flash('error', 'Image upload failed. Ensure file type and size are valid.');
                $this->redirect('/vendor/items');
            }
            $image = $img;
        }

        $data = [
            'vendor_id' => (int)$vendor['id'],
            'category_id' => (int)$categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'discount_percent' => $discount,
            'expiry_date' => $expiry,
            'stock' => $stock,
            'image_path' => $image,
            'status' => 'active',
        ];
        try {
            (new FoodItem())->create($data);
            Session::flash('success', 'Item created successfully');
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to create item');
        }
        $this->redirect('/vendor/items');
    }
}
