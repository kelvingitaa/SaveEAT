<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Order;
use App\Models\DeliveryDriver;
use App\Models\Shelter;
use App\Models\VendorVerification;
use App\Core\CSRF;
use PDO;

class AdminController extends Controller
{
    public function items(): void
    {
        $db = (new \App\Models\FoodItem())->getDb();
        try {
            $rows = $db->query('SELECT * FROM food_items ORDER BY created_at DESC')->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to load food items.');
            $rows = [];
        }
        $this->view('admin/items', ['items' => $rows]);
    }
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
        $revenue = (float)$db->query("SELECT SUM(total_price) FROM orders WHERE status IN ('paid','completed')")->fetchColumn() ?: 0.0;
        $topItems = $db->query("SELECT fi.name, COUNT(oi.id) as sold FROM order_items oi JOIN food_items fi ON fi.id = oi.food_item_id GROUP BY fi.id ORDER BY sold DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $expiringItems = $db->query("SELECT name, expiry_date FROM food_items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        $suspendedVendors = $db->query("SELECT business_name FROM vendors WHERE status='suspended'")->fetchAll(PDO::FETCH_ASSOC);
        $pendingVendors = $db->query("SELECT business_name FROM vendors WHERE approved=0 AND status='pending'")->fetchAll(PDO::FETCH_ASSOC);
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
            'pendingVendors' => $pendingVendors,
            'users' => $users
        ]);
    }

    public function users(): void
    {
    // Auth::requireRole(['admin']);
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

    public function createUser(): void
    {
        Auth::requireRole(['admin']);
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/admin/users');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/users');
        }
        
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role = (string)($_POST['role'] ?? 'consumer');
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/admin/users');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email format');
            $this->redirect('/admin/users');
        }
        
        if (!in_array($role, ['admin', 'vendor', 'consumer'])) {
            Session::flash('error', 'Invalid role selected');
            $this->redirect('/admin/users');
        }
        
        try {
            $userModel = new User();
            
            // Check if email already exists
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser) {
                Session::flash('error', 'Email already exists');
                $this->redirect('/admin/users');
            }
            
            // Create user
            $userId = $userModel->create([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => 'active'
            ]);
            
            Session::flash('success', 'User created successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to create user');
        }
        
        $this->redirect('/admin/users');
    }

    public function updateUser(): void
    {
        Auth::requireRole(['admin']);
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/admin/users');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/users');
        }
        
        $userId = (int)($_POST['user_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $role = (string)($_POST['role'] ?? 'consumer');
        $status = (string)($_POST['status'] ?? 'active');
        
        // Validation
        if (empty($name) || empty($email) || $userId <= 0) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/admin/users');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email format');
            $this->redirect('/admin/users');
        }
        
        if (!in_array($role, ['admin', 'vendor', 'consumer'])) {
            Session::flash('error', 'Invalid role selected');
            $this->redirect('/admin/users');
        }
        
        if (!in_array($status, ['active', 'suspended', 'pending'])) {
            Session::flash('error', 'Invalid status selected');
            $this->redirect('/admin/users');
        }
        
        try {
            $userModel = new User();
            $db = $userModel->getDb();
            
            // Check if email already exists for another user
            $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
            $stmt->execute(['email' => $email, 'id' => $userId]);
            if ($stmt->fetchColumn()) {
                Session::flash('error', 'Email already exists for another user');
                $this->redirect('/admin/users');
            }
            
            // Update user
            $stmt = $db->prepare('UPDATE users SET name = :name, email = :email, role = :role, status = :status, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status,
                'id' => $userId
            ]);
            
            Session::flash('success', 'User updated successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update user');
        }
        
        $this->redirect('/admin/users');
    }

    public function toggleUserStatus(): void
    {
        Auth::requireRole(['admin']);
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/admin/users');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/users');
        }
        
        $userId = (int)($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            Session::flash('error', 'Invalid user specified');
            $this->redirect('/admin/users');
        }
        
        try {
            $userModel = new User();
            $db = $userModel->getDb();
            
            // Get current status
            $stmt = $db->prepare('SELECT status FROM users WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $currentStatus = $stmt->fetchColumn();
            
            if (!$currentStatus) {
                Session::flash('error', 'User not found');
                $this->redirect('/admin/users');
            }
            
            // Toggle status between active and suspended
            $newStatus = $currentStatus === 'active' ? 'suspended' : 'active';
            
            $stmt = $db->prepare('UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['status' => $newStatus, 'id' => $userId]);
            
            Session::flash('success', "User " . ($newStatus === 'suspended' ? 'suspended' : 'activated') . " successfully");
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update user status');
        }
        
        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        Auth::requireRole(['admin']);
        
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            Session::flash('error', 'Method not allowed');
            $this->redirect('/admin/users');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            http_response_code(419);
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/admin/users');
        }
        
        $userId = (int)($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            Session::flash('error', 'Invalid user specified');
            $this->redirect('/admin/users');
        }
        
        try {
            $userModel = new User();
            $db = $userModel->getDb();
            
            // Delete user
            $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            
            Session::flash('success', 'User deleted successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to delete user');
        }
        
        $this->redirect('/admin/users');
    }

public function createVendor(): void
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
    
    $businessName = trim((string)($_POST['business_name'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $contactPhone = trim((string)($_POST['contact_phone'] ?? ''));
    $userId = (int)($_POST['user_id'] ?? 0);
    
    // Validation
    if (empty($businessName) || empty($location) || empty($contactPhone) || $userId <= 0) {
        Session::flash('error', 'All fields are required');
        $this->redirect('/admin/vendors');
    }
    
    try {
        $vendorModel = new Vendor();
        $db = $vendorModel->getDb();
        
        // Check if user exists and is a vendor
        $userStmt = $db->prepare('SELECT id, role FROM users WHERE id = :user_id');
        $userStmt->execute(['user_id' => $userId]);
        $user = $userStmt->fetch();
        
        if (!$user) {
            Session::flash('error', 'User not found');
            $this->redirect('/admin/vendors');
        }
        
        if ($user['role'] !== 'vendor') {
            Session::flash('error', 'User must have vendor role');
            $this->redirect('/admin/vendors');
        }
        
        // Check if vendor already exists for this user
        $vendorStmt = $db->prepare('SELECT id FROM vendors WHERE user_id = :user_id');
        $vendorStmt->execute(['user_id' => $userId]);
        if ($vendorStmt->fetchColumn()) {
            Session::flash('error', 'Vendor already exists for this user');
            $this->redirect('/admin/vendors');
        }
        
        // Create vendor - auto-approve when created by admin
        $stmt = $db->prepare('INSERT INTO vendors (user_id, business_name, location, contact_phone, approved, status, created_at, updated_at) VALUES (:user_id, :business_name, :location, :contact_phone, :approved, :status, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'business_name' => $businessName,
            'location' => $location,
            'contact_phone' => $contactPhone,
            'approved' => 1,
            'status' => 'active'
        ]);
        
        // ALSO update the user status to active
        $stmt = $db->prepare('UPDATE users SET status = "active", updated_at = NOW() WHERE id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        
        Session::flash('success', 'Vendor created successfully');
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to create vendor: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/vendors');
}

    public function updateVendor(): void
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
        
        $vendorId = (int)($_POST['vendor_id'] ?? 0);
        $businessName = trim((string)($_POST['business_name'] ?? ''));
        $location = trim((string)($_POST['location'] ?? ''));
        $contactPhone = trim((string)($_POST['contact_phone'] ?? ''));
        
        // Validation
        if (empty($businessName) || empty($location) || empty($contactPhone) || $vendorId <= 0) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/admin/vendors');
        }
        
        try {
            $vendorModel = new Vendor();
            $db = $vendorModel->getDb();
            
            // Update vendor
            $stmt = $db->prepare('UPDATE vendors SET business_name = :business_name, location = :location, contact_phone = :contact_phone, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'business_name' => $businessName,
                'location' => $location,
                'contact_phone' => $contactPhone,
                'id' => $vendorId
            ]);
            
            Session::flash('success', 'Vendor updated successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update vendor');
        }
        
        $this->redirect('/admin/vendors');
    }

    public function toggleVendorStatus(): void
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
        
        $vendorId = (int)($_POST['vendor_id'] ?? 0);
        
        if ($vendorId <= 0) {
            Session::flash('error', 'Invalid vendor specified');
            $this->redirect('/admin/vendors');
        }
        
        try {
            $vendorModel = new Vendor();
            $db = $vendorModel->getDb();
            
            // Get current status
            $stmt = $db->prepare('SELECT status FROM vendors WHERE id = :id');
            $stmt->execute(['id' => $vendorId]);
            $currentStatus = $stmt->fetchColumn();
            
            if (!$currentStatus) {
                Session::flash('error', 'Vendor not found');
                $this->redirect('/admin/vendors');
            }
            
            // Toggle between active and suspended (only if vendor is approved)
            $stmt = $db->prepare('SELECT approved FROM vendors WHERE id = :id');
            $stmt->execute(['id' => $vendorId]);
            $isApproved = (bool)$stmt->fetchColumn();
            
            if (!$isApproved) {
                Session::flash('error', 'Cannot suspend/activate a pending vendor. Please approve first.');
                $this->redirect('/admin/vendors');
            }
            
            $newStatus = $currentStatus === 'active' ? 'suspended' : 'active';
            
            $stmt = $db->prepare('UPDATE vendors SET status = :status, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['status' => $newStatus, 'id' => $vendorId]);
            
            Session::flash('success', "Vendor " . ($newStatus === 'suspended' ? 'suspended' : 'activated') . " successfully");
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update vendor status');
        }
        
        $this->redirect('/admin/vendors');
    }

    public function deleteVendor(): void
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
        
        $vendorId = (int)($_POST['vendor_id'] ?? 0);
        
        if ($vendorId <= 0) {
            Session::flash('error', 'Invalid vendor specified');
            $this->redirect('/admin/vendors');
        }
        
        try {
            $vendorModel = new Vendor();
            $db = $vendorModel->getDb();
            
            // Delete vendor
            $stmt = $db->prepare('DELETE FROM vendors WHERE id = :id');
            $stmt->execute(['id' => $vendorId]);
            
            Session::flash('success', 'Vendor deleted successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to delete vendor');
        }
        
        $this->redirect('/admin/vendors');
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
        $vendorModel = new Vendor();
        $db = $vendorModel->getDb();
        
        // Get the user_id for this vendor first
        $stmt = $db->prepare('SELECT user_id FROM vendors WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vendor) {
            Session::flash('error', 'Vendor not found');
            $this->redirect('/admin/vendors');
        }
        
        $userId = $vendor['user_id'];
        
        // Update both vendors table AND users table
        $stmt = $db->prepare('UPDATE vendors SET approved = 1, status = "active", updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
        
        // ALSO update the user status to active
        $stmt = $db->prepare('UPDATE users SET status = "active", updated_at = NOW() WHERE id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        
        Session::flash('success', 'Vendor approved successfully');
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to approve vendor: ' . $e->getMessage());
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
        $status = $_GET['status'] ?? '';
        $approved = $_GET['approved'] ?? '';
        $search = $_GET['q'] ?? '';
        
        try {
            // Build WHERE conditions
            $whereConditions = [];
            $params = [];
            
            if (!empty($status)) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $status;
            }
            
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
            
            $sql = "SELECT v.*, u.email, u.name as owner_name 
                   FROM vendors v 
                   LEFT JOIN users u ON v.user_id = u.id 
                   $whereClause 
                   ORDER BY v.created_at DESC 
                   LIMIT :limit OFFSET :offset";
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
    // Auth::requireRole(['admin']);
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
            
            $stmt = $db->prepare('INSERT INTO categories (name, description, created_at, updated_at) VALUES (:name, :description, NOW(), NOW())');
            $stmt->execute(['name' => $name, 'description' => $description]);
            
            Session::flash('success', 'Category created successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to save category: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/categories');
    }

    public function categoryUpdate(): void
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
        
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        
        if ($categoryId <= 0 || empty($name)) {
            Session::flash('error', 'Category ID and name are required');
            $this->redirect('/admin/categories');
        }
        
        $db = (new Category())->getDb();
        try {
            // Check if name already exists for another category
            $chk = $db->prepare('SELECT id FROM categories WHERE name = :name AND id != :id LIMIT 1');
            $chk->execute(['name' => $name, 'id' => $categoryId]);
            if ($chk->fetchColumn()) {
                Session::flash('error', 'Category with that name already exists');
                $this->redirect('/admin/categories');
            }
            
            $stmt = $db->prepare('UPDATE categories SET name = :name, description = :description, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['name' => $name, 'description' => $description, 'id' => $categoryId]);
            
            Session::flash('success', 'Category updated successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to update category: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/categories');
    }

    public function categoryDelete(): void
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
        
        $categoryId = (int)($_POST['category_id'] ?? 0);
        
        if ($categoryId <= 0) {
            Session::flash('error', 'Invalid category specified');
            $this->redirect('/admin/categories');
        }
        
        $db = (new Category())->getDb();
        try {
            // Check if category is being used by any food items
            $checkUsage = $db->prepare('SELECT COUNT(*) FROM food_items WHERE category_id = :category_id');
            $checkUsage->execute(['category_id' => $categoryId]);
            $usageCount = $checkUsage->fetchColumn();
            
            if ($usageCount > 0) {
                Session::flash('error', 'Cannot delete category. It is being used by ' . $usageCount . ' food item(s).');
                $this->redirect('/admin/categories');
            }
            
            $stmt = $db->prepare('DELETE FROM categories WHERE id = :id');
            $stmt->execute(['id' => $categoryId]);
            
            Session::flash('success', 'Category deleted successfully');
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to delete category: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/categories');
    }

    public function shelters(): void
{
    Auth::requireRole(['admin']);
    $shelterModel = new Shelter();
    
    try {
        $pendingShelters = $shelterModel->getPendingVerifications();
        $activeShelters = $shelterModel->getActiveShelters();
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to load shelters.');
        $pendingShelters = [];
        $activeShelters = [];
    }
    
    $this->view('admin/shelters', [
        'pendingShelters' => $pendingShelters,
        'activeShelters' => $activeShelters
    ]);
}

public function approveShelter(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/shelters');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/shelters');
    }
    
    $shelterId = (int)($_POST['shelter_id'] ?? 0);
    
    if ($shelterId <= 0) {
        Session::flash('error', 'Invalid shelter specified');
        $this->redirect('/admin/shelters');
    }
    
    try {
        $shelterModel = new Shelter();
        $success = $shelterModel->approve($shelterId, Auth::userId());
        
        if ($success) {
            Session::flash('success', 'Shelter approved successfully');
        } else {
            Session::flash('error', 'Failed to approve shelter');
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to approve shelter: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/shelters');
}

public function vendorVerifications(): void
{
    Auth::requireRole(['admin']);
    $verificationModel = new VendorVerification();
    
    try {
        $pendingVerifications = $verificationModel->getPendingVerifications();
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to load vendor verifications.');
        $pendingVerifications = [];
    }
    
    $this->view('admin/vendor-verifications', [
        'verifications' => $pendingVerifications
    ]);
}

public function approveVendorVerification(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/verifications');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/verifications');
    }
    
    $verificationId = (int)($_POST['verification_id'] ?? 0);
    
    if ($verificationId <= 0) {
        Session::flash('error', 'Invalid verification specified');
        $this->redirect('/admin/verifications');
    }
    
    try {
        $verificationModel = new VendorVerification();
        $success = $verificationModel->approve($verificationId, Auth::userId());
        
        if ($success) {
            Session::flash('success', 'Vendor verification approved successfully');
        } else {
            Session::flash('error', 'Failed to approve vendor verification');
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to approve verification: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/verifications');
}

public function rejectVendorVerification(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/verifications');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/verifications');
    }
    
    $verificationId = (int)($_POST['verification_id'] ?? 0);
    
    if ($verificationId <= 0) {
        Session::flash('error', 'Invalid verification specified');
        $this->redirect('/admin/verifications');
    }
    
    try {
        $verificationModel = new VendorVerification();
        $success = $verificationModel->reject($verificationId, Auth::userId());
        
        if ($success) {
            Session::flash('success', 'Vendor verification rejected');
        } else {
            Session::flash('error', 'Failed to reject vendor verification');
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to reject verification: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/verifications');
}
public function reports(): void
{
    Auth::requireRole(['admin']);
    
    $db = (new User())->getDb();
    
    try {
        // Food Saved Report
        $foodSavedStmt = $db->prepare("
            SELECT 
                COUNT(*) as total_items_saved,
                SUM(fi.price * oi.quantity) as total_value_saved,
                AVG(fi.price * oi.quantity) as avg_value_per_item,
                COUNT(DISTINCT fi.vendor_id) as vendors_contributing
            FROM order_items oi
            LEFT JOIN food_items fi ON oi.food_item_id = fi.id
            WHERE fi.expiry_date >= CURDATE()
        ");
        $foodSavedStmt->execute();
        $foodSaved = $foodSavedStmt->fetch(PDO::FETCH_ASSOC);
        
        // Donations Report
        $donationsStmt = $db->prepare("
            SELECT 
                COUNT(*) as total_donations,
                SUM(quantity) as items_donated,
                COUNT(DISTINCT vendor_id) as donating_vendors,
                COUNT(DISTINCT shelter_id) as supported_shelters
            FROM donations 
            WHERE status IN ('completed', 'scheduled')
            AND donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $donationsStmt->execute();
        $donations = $donationsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Vendor Income Report
        $incomeStmt = $db->prepare("
            SELECT 
                v.business_name,
                COUNT(o.id) as orders_completed,
                SUM(o.total_price) as total_income,
                AVG(o.total_price) as avg_order_value
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN food_items fi ON oi.food_item_id = fi.id
            LEFT JOIN vendors v ON fi.vendor_id = v.id
            WHERE o.status IN ('completed', 'paid')
            AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY v.id, v.business_name
            ORDER BY total_income DESC
            LIMIT 10
        ");
        $incomeStmt->execute();
        $vendorIncome = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Monthly Trends
        $monthlyStmt = $db->prepare("
            SELECT 
                DATE_FORMAT(o.created_at, '%Y-%m') as month,
                COUNT(o.id) as order_count,
                SUM(o.total_price) as revenue,
                COUNT(DISTINCT d.id) as donation_count
            FROM orders o
            LEFT JOIN donations d ON DATE_FORMAT(d.created_at, '%Y-%m') = DATE_FORMAT(o.created_at, '%Y-%m')
            WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
            ORDER BY month DESC
        ");
        $monthlyStmt->execute();
        $monthlyTrends = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (\Throwable $e) {
        Session::flash('error', 'Failed to generate reports: ' . $e->getMessage());
        $foodSaved = $donations = [];
        $vendorIncome = $monthlyTrends = [];
    }
    
    $this->view('admin/reports', [
        'foodSaved' => $foodSaved,
        'donations' => $donations,
        'vendorIncome' => $vendorIncome,
        'monthlyTrends' => $monthlyTrends
    ]);
}


public function foodItems(): void
{
    Auth::requireRole(['admin']);
    $db = (new User())->getDb();
    
    try {
        $foodItems = $db->query("
            SELECT fi.*, v.business_name, c.name as category_name 
            FROM food_items fi 
            LEFT JOIN vendors v ON fi.vendor_id = v.id 
            LEFT JOIN categories c ON fi.category_id = c.id 
            ORDER BY fi.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to load food items.');
        $foodItems = [];
    }
    
    $this->view('admin/items', ['foodItems' => $foodItems]);
}

public function orders(): void
{
    Auth::requireRole(['admin']);
    $db = (new User())->getDb();
    
    try {
        $orders = $db->query("
            SELECT o.*, u.name as customer_name, u.email, u.phone 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to load orders.');
        $orders = [];
    }
    
    $this->view('admin/orders', ['orders' => $orders]);
}

public function logs(): void
{
    Auth::requireRole(['admin']);
    // For now, return empty logs - you can implement this later
    $this->view('admin/logs', ['logs' => []]);
}

// Add these approval methods to your AdminController class

public function approvals(): void
{
    Auth::requireRole(['admin']);
    
    try {
        $db = (new User())->getDb();
        
        // Get pending vendors
        $pendingVendors = $db->query("
            SELECT v.*, u.email, u.name as owner_name 
            FROM vendors v 
            LEFT JOIN users u ON v.user_id = u.id 
            WHERE v.approved = 0 
            ORDER BY v.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get pending drivers
        $pendingDrivers = $db->query("
            SELECT dd.*, u.email, u.name, u.phone, u.address
            FROM delivery_drivers dd 
            LEFT JOIN users u ON dd.user_id = u.id 
            WHERE dd.status = 'pending'
            ORDER BY dd.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get pending shelters
        $pendingShelters = $db->query("
            SELECT s.*, u.email, u.name as contact_person 
            FROM shelters s 
            LEFT JOIN users u ON s.user_id = u.id 
            WHERE s.verified = 0 
            ORDER BY s.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to load pending approvals.');
        $pendingVendors = [];
        $pendingDrivers = [];
        $pendingShelters = [];
    }
    
    $this->view('admin/approvals', [
        'pendingVendors' => $pendingVendors,
        'pendingDrivers' => $pendingDrivers,
        'pendingShelters' => $pendingShelters
    ]);
}

public function approveDriver(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/approvals');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/approvals');
    }
    
    $driverId = (int)($_POST['driver_id'] ?? 0);
    
    if ($driverId <= 0) {
        Session::flash('error', 'Invalid driver specified');
        $this->redirect('/admin/approvals');
    }
    
    try {
        $driverModel = new DeliveryDriver();
        $db = $driverModel->getDb();
        
        // Update driver status to available
        $stmt = $db->prepare('UPDATE delivery_drivers SET status = "available" WHERE id = :id');
        $stmt->execute(['id' => $driverId]);
        
        Session::flash('success', 'Driver approved successfully');
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to approve driver: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/approvals');
}

public function rejectVendor(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/approvals');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/approvals');
    }
    
    $vendorId = (int)($_POST['vendor_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($vendorId <= 0) {
        Session::flash('error', 'Invalid vendor specified');
        $this->redirect('/admin/approvals');
    }
    
    try {
        $vendorModel = new Vendor();
        $db = $vendorModel->getDb();
        
        // Delete vendor and associated user
        $stmt = $db->prepare('SELECT user_id FROM vendors WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($vendor) {
            // Delete vendor
            $stmt = $db->prepare('DELETE FROM vendors WHERE id = :id');
            $stmt->execute(['id' => $vendorId]);
            
            // Also delete the user account
            $stmt = $db->prepare('DELETE FROM users WHERE id = :user_id');
            $stmt->execute(['user_id' => $vendor['user_id']]);
        }
        
        Session::flash('success', 'Vendor registration rejected');
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to reject vendor: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/approvals');
}

public function rejectDriver(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/approvals');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/approvals');
    }
    
    $driverId = (int)($_POST['driver_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($driverId <= 0) {
        Session::flash('error', 'Invalid driver specified');
        $this->redirect('/admin/approvals');
    }
    
    try {
        $driverModel = new DeliveryDriver();
        $db = $driverModel->getDb();
        
        // Delete driver and associated user
        $stmt = $db->prepare('SELECT user_id FROM delivery_drivers WHERE id = :id');
        $stmt->execute(['id' => $driverId]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($driver) {
            // Delete driver
            $stmt = $db->prepare('DELETE FROM delivery_drivers WHERE id = :id');
            $stmt->execute(['id' => $driverId]);
            
            // Also delete the user account
            $stmt = $db->prepare('DELETE FROM users WHERE id = :user_id');
            $stmt->execute(['user_id' => $driver['user_id']]);
        }
        
        Session::flash('success', 'Driver registration rejected');
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to reject driver: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/approvals');
}

public function rejectShelter(): void
{
    Auth::requireRole(['admin']);
    
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        Session::flash('error', 'Method not allowed');
        $this->redirect('/admin/approvals');
    }
    
    $token = $_POST['_csrf'] ?? null;
    if (!$token || !CSRF::check($token)) {
        http_response_code(419);
        Session::flash('error', 'Invalid CSRF token');
        $this->redirect('/admin/approvals');
    }
    
    $shelterId = (int)($_POST['shelter_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($shelterId <= 0) {
        Session::flash('error', 'Invalid shelter specified');
        $this->redirect('/admin/approvals');
    }
    
    try {
        $shelterModel = new Shelter();
        $db = $shelterModel->getDb();
        
        // Delete shelter and associated user
        $stmt = $db->prepare('SELECT user_id FROM shelters WHERE id = :id');
        $stmt->execute(['id' => $shelterId]);
        $shelter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($shelter) {
            // Delete shelter
            $stmt = $db->prepare('DELETE FROM shelters WHERE id = :id');
            $stmt->execute(['id' => $shelterId]);
            
            // Also delete the user account
            $stmt = $db->prepare('DELETE FROM users WHERE id = :user_id');
            $stmt->execute(['user_id' => $shelter['user_id']]);
        }
        
        Session::flash('success', 'Shelter registration rejected');
        
    } catch (\Throwable $e) {
        http_response_code(500);
        Session::flash('error', 'Failed to reject shelter: ' . $e->getMessage());
    }
    
    $this->redirect('/admin/approvals');
}
}