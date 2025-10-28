<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Validator;
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\User;
use App\Models\Vendor;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login');
    }

    public function login(): void
{
    if (!CSRF::check($_POST['_csrf'] ?? '')) { echo 'Invalid CSRF'; return; }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!Validator::email($email) || !Validator::min($password, 6)) {
        $this->view('auth/login', ['error' => 'Invalid credentials']);
        return;
    }
    $userModel = new User();
    $u = $userModel->findByEmail($email);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        $this->view('auth/login', ['error' => 'Invalid credentials']);
        return;
    }
    if ($u['status'] !== 'active') {
        $this->view('auth/login', ['error' => 'Account not active']);
        return;
    }
    Auth::attempt($u);
    
    if ($u['role'] === 'admin') $this->redirect('/admin');
    if ($u['role'] === 'vendor') $this->redirect('/vendor');
    if ($u['role'] === 'driver') $this->redirect('/delivery/dashboard');
    if ($u['role'] === 'shelter') $this->redirect('/shelter/dashboard');
    $this->redirect('/consumer');
}
    public function showRegister(): void
    {
        $this->view('auth/register');
    }

    public function register(): void
    {
        if (!CSRF::check($_POST['_csrf'] ?? '')) { echo 'Invalid CSRF'; return; }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'consumer';

        if (!Validator::required($name) || !Validator::email($email) || !Validator::min($password, 8)) {
            $this->view('auth/register', ['error' => 'Invalid input']);
            return;
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $this->view('auth/register', ['error' => 'Email already registered']);
            return;
        }
        $status = ($role === 'vendor') ? 'pending' : 'active';
        $uid = $userModel->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => $status,
        ]);

        if ($role === 'vendor') {
            (new Vendor())->create($uid, []);
        }

        $this->view('auth/login', ['success' => 'Registration successful. Await approval if vendor.']);
    }



public function showConsumerRegistration(): void
{
    $this->view('auth/register-consumer');
}

public function showVendorRegistration(): void
{
    $this->view('auth/register-vendor');
}

public function showShelterRegistration(): void
{
    $this->view('auth/register-shelter');
}

public function registerConsumer(): void
{
    // Your existing register logic but only for consumers
    if (!CSRF::check($_POST['_csrf'] ?? '')) { 
        $this->view('auth/register-consumer', ['error' => 'Invalid CSRF token']);
        return; 
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!Validator::required($name) || !Validator::email($email) || !Validator::min($password, 8)) {
        $this->view('auth/register-consumer', ['error' => 'All fields are required and password must be at least 8 characters']);
        return;
    }

    $userModel = new User();
    if ($userModel->findByEmail($email)) {
        $this->view('auth/register-consumer', ['error' => 'Email already registered']);
        return;
    }

    $uid = $userModel->create([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'consumer',
        'status' => 'active',
    ]);

    $this->view('auth/login', ['success' => 'Registration successful! Please login.']);
}

public function registerVendor(): void
{
    if (!CSRF::check($_POST['_csrf'] ?? '')) { 
        $this->view('auth/register-vendor', ['error' => 'Invalid CSRF token']);
        return; 
    }
    
    $businessName = trim($_POST['business_name'] ?? '');
    $contactName = trim($_POST['contact_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $businessType = $_POST['business_type'] ?? 'restaurant';

    // Validation
    if (!Validator::required($businessName) || !Validator::required($contactName) || 
        !Validator::email($email) || !Validator::min($password, 8)) {
        $this->view('auth/register-vendor', ['error' => 'All fields are required and password must be at least 8 characters']);
        return;
    }

    $userModel = new User();
    if ($userModel->findByEmail($email)) {
        $this->view('auth/register-vendor', ['error' => 'Email already registered']);
        return;
    }

    // Handle license file upload
    $licensePath = null;
    if (isset($_FILES['license_file']) && $_FILES['license_file']['error'] === UPLOAD_ERR_OK) {
        $uploader = new \App\Core\Uploader();
        $licensePath = $uploader->upload($_FILES['license_file'], 'licenses');
    }

    // Create user
    $uid = $userModel->create([
        'name' => $contactName,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'vendor',
        'status' => 'pending', // Vendors need approval
    ]);

    // Create vendor record
    $vendorModel = new Vendor();
    $vendorModel->create($uid, [
        'business_name' => $businessName,
        'business_type' => $businessType,
        'address' => $address,
        'phone' => $phone,
        'license_file' => $licensePath
    ]);

    $this->view('auth/login', ['success' => 'Vendor registration submitted! Please wait for approval before logging in.']);
}

// Add to AuthController

public function showRegisterSelect(): void
{
    $this->view('auth/register-select');
}

public function showDriverRegistration(): void
{
    $this->view('auth/register-driver');
}

public function registerDriver(): void
{
    if (!CSRF::check($_POST['_csrf'] ?? '')) { 
        $this->view('auth/register-driver', ['error' => 'Invalid CSRF token']);
        return; 
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $vehicleType = $_POST['vehicle_type'] ?? '';
    $licensePlate = trim($_POST['license_plate'] ?? '');

    // Validation
    if (!Validator::required($name) || !Validator::email($email) || 
        !Validator::min($password, 8) || !Validator::required($vehicleType)) {
        $this->view('auth/register-driver', ['error' => 'All required fields must be filled and password must be at least 8 characters']);
        return;
    }

    $userModel = new User();
    if ($userModel->findByEmail($email)) {
        $this->view('auth/register-driver', ['error' => 'Email already registered']);
        return;
    }

    // Handle license file upload
    $licensePath = null;
    if (isset($_FILES['license_file']) && $_FILES['license_file']['error'] === UPLOAD_ERR_OK) {
        $uploader = new \App\Core\Uploader();
        $licensePath = $uploader->upload($_FILES['license_file'], 'drivers');
    }

    // Create user
    $uid = $userModel->create([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'driver',
        'status' => 'pending', // Drivers need approval
        'phone' => $phone,
        'address' => $address
    ]);

    // Create driver record
    $driverModel = new \App\Models\DeliveryDriver();
    $driverModel->create($uid, [
        'vehicle_type' => $vehicleType,
        'license_plate' => $licensePlate,
        'license_file' => $licensePath
    ]);

    $this->view('auth/login', ['success' => 'Driver registration submitted! Please wait for approval.']);
}
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
