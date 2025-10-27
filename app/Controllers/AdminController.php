<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Order;
use App\Core\CSRF;
use PDO;

class AdminController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $userModel = new User();
        $db = $userModel->getDb();
        $userCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $vendorCount = (int)$db->query("SELECT COUNT(*) FROM vendors")->fetchColumn();
        $consumerCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='consumer'")->fetchColumn();
        $foodCount = (int)$db->query("SELECT COUNT(*) FROM food_items")->fetchColumn();
        $foodActive = (int)$db->query("SELECT COUNT(*) FROM food_items WHERE status='active'")->fetchColumn();
        $foodInactive = (int)$db->query("SELECT COUNT(*) FROM food_items WHERE status='inactive'")->fetchColumn();
        $foodExpired = (int)$db->query("SELECT COUNT(*) FROM food_items WHERE status='expired'")->fetchColumn();
        $orderCount = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $revenue = (float)$db->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('paid','completed')")->fetchColumn() ?: 0.0;
        $topItems = $db->query("SELECT fi.name, COUNT(oi.id) as sold FROM order_items oi JOIN food_items fi ON fi.id = oi.food_item_id GROUP BY fi.id ORDER BY sold DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $expiringItems = $db->query("SELECT name, expiry_date FROM food_items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        $suspendedVendors = $db->query("SELECT business_name FROM vendors WHERE approved=0")->fetchAll(PDO::FETCH_ASSOC);
        $users = $db->query("SELECT name,email,role,status FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        $this->view('admin/dashboard', [
            'userCount' => $userCount,
            'vendorCount' => $vendorCount,
            'consumerCount' => $consumerCount,
            'foodCount' => $foodCount,
            'foodActive' => $foodActive,
            'foodInactive' => $foodInactive,
            'foodExpired' => $foodExpired,
            'orderCount' => $orderCount,
            'revenue' => $revenue,
            'topItems' => $topItems,
            'expiringItems' => $expiringItems,
            'suspendedVendors' => $suspendedVendors,
            'users' => $users
        ]);
    }

    public function users(): void
    {
        Auth::requireRole(['admin']);
        $m = new User();
        $db = $m->getDb();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filter parameters
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['q'] ?? '';
        
        try {
            // Build WHERE conditions
            $whereConditions = [];
            $params = [];
            
            if (!empty($role)) {
                $whereConditions[] = 'role = :role';
                $params['role'] = $role;
            }
            
            if (!empty($status)) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $status;
            }
            
            if (!empty($search)) {
                $whereConditions[] = '(name LIKE :search OR email LIKE :search)';
                $params['search'] = '%' . $search . '%';
            }
            
            // Build SQL queries
            $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Count total with filters
            $countSql = "SELECT COUNT(*) FROM users $whereClause";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            
            // Get users with filters
            $sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            
            // Bind filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to load users.');
            $rows = [];
            $total = 0;
        }
        
        $this->view('admin/users', [
            'users' => $rows,
            'page' => $page,
            'pages' => (int)ceil(($total ?: 1)/$perPage)
        ]);
    }

    public function approveVendor(): void
    {
        Auth::requireRole(['admin']);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/admin/vendors');
        }
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/vendors');
        }
        $vendorId = filter_input(INPUT_POST, 'vendor_id', FILTER_VALIDATE_INT) ?: 0;
        if ($vendorId <= 0) {
            Session::flash('error', 'Invalid vendor specified');
            $this->redirect('/admin/vendors');
        }
        try {
            (new Vendor())->approve($vendorId);
            Session::flash('success', 'Vendor approved successfully');
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to approve vendor');
        }
        $this->redirect('/admin/vendors');
    }

    public function vendors(): void
    {
        Auth::requireRole(['admin']);
        $m = new Vendor();
        $db = $m->getDb();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filter parameters
        $approved = $_GET['approved'] ?? '';
        $search = $_GET['q'] ?? '';
        
        try {
            // Build WHERE conditions
            $whereConditions = [];
            $params = [];
            
            if ($approved !== '') {
                $whereConditions[] = 'approved = :approved';
                $params['approved'] = (bool)$approved;
            }
            
            if (!empty($search)) {
                $whereConditions[] = '(business_name LIKE :search OR location LIKE :search)';
                $params['search'] = '%' . $search . '%';
            }
            
            // Build SQL queries
            $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $countSql = "SELECT COUNT(*) FROM vendors $whereClause";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            
            $sql = "SELECT * FROM vendors $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to load vendors.');
            $rows = [];
            $total = 0;
        }
        
        $this->view('admin/vendors', [
            'vendors' => $rows,
            'page' => $page,
            'pages' => (int)ceil(($total ?: 1)/$perPage)
        ]);
    }

    public function categories(): void
    {
        Auth::requireRole(['admin']);
        $db = (new Category())->getDb();
        try {
            $rows = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to load categories.');
            $rows = [];
        }
        $this->view('admin/categories', ['categories' => $rows]);
    }

    public function categoryStore(): void
    {
        Auth::requireRole(['admin']);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/admin/categories');
        }
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/categories');
        }
        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        if ($name === '') {
            Session::flash('error', 'Category name is required');
            $this->redirect('/admin/categories');
        }
        $db = (new Category())->getDb();
        try {
            // prevent duplicates
            $chk = $db->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
            $chk->execute(['name' => $name]);
            if ($chk->fetchColumn()) {
                Session::flash('error', 'Category with that name already exists');
                $this->redirect('/admin/categories');
            }
            $stmt = $db->prepare('INSERT INTO categories (name,description,created_at,updated_at) VALUES (:name,:description,NOW(),NOW())');
            $stmt->execute(['name' => $name, 'description' => $description]);
            Session::flash('success', 'Category created');
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to save category');
        }
        $this->redirect('/admin/categories');
    }
}