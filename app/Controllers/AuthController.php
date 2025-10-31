<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Validator;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session;
use App\Models\User;
use App\Models\Vendor;
use App\Services\TwoFactorService;

class AuthController extends Controller
{
    private $twoFactorService;

    public function __construct()
    {
        $this->twoFactorService = new TwoFactorService();
    }

    public function showLogin(): void
    {
        $this->view('auth/login');
    }

public function login(): void
{
    if (!CSRF::check($_POST['_csrf'] ?? '')) {
        $this->view('auth/login', ['error' => 'Invalid CSRF token']);
        return;
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $this->view('auth/login', ['error' => 'Invalid credentials']);
        return;
    }

    if ($user['status'] !== 'active') {
        $this->view('auth/login', ['error' => 'Account not active']);
        return;
    }

    if ($user['role'] === 'consumer' && !Session::get('email_verified_' . $user['id'])) {
        $code = $this->twoFactorService->initiateTwoFactor($user['id'], $user['email']);

        // ✅ FIX: set session before showing debug page
        Session::set('pending_user_id', $user['id']);
        Session::set('pending_user_data', $user);

        if (APP_DEBUG) {
            $this->view('auth/verify-2fa', [
                'email' => $user['email'],
                'debug_code' => $code,
                'success' => 'Verification code sent. Debug code: ' . $code
            ]);
            return;
        }

        $this->view('auth/verify-2fa', [
            'email' => $user['email'],
            'success' => 'Verification code sent to your email'
        ]);
        return;
    }

    $this->completeLogin($user);
}

  public function verifyTwoFactor(): void
{
    if (!CSRF::check($_POST['_csrf'] ?? '')) {
        $this->view('auth/verify-2fa', ['error' => 'Invalid CSRF token']);
        return;
    }

    $code = trim($_POST['code'] ?? '');
    $userId = Session::get('pending_user_id');
    $userData = Session::get('pending_user_data');

    if (!$userId || !$userData) {
        $this->redirect('/login');
        return;
    }

    if (!Validator::required($code) || !preg_match('/^\d{6}$/', $code)) {
        $this->view('auth/verify-2fa', [
            'email' => $userData['email'],
            'error' => 'Please enter a valid 6-digit code'
        ]);
        return;
    }

    if ($this->twoFactorService->verifyCode($userId, $code)) {
        // Mark session as verified
        Session::set('email_verified_' . $userId, true);
        Session::remove('pending_user_id');
        Session::remove('pending_user_data');

        $this->completeLogin($userData);
    } else {
        $this->view('auth/verify-2fa', [
            'email' => $userData['email'],
            'error' => 'Invalid or expired verification code'
        ]);
    }
}


    public function resendCode(): void
    {
        if (!CSRF::check($_POST['_csrf'] ?? '')) { 
            echo 'Invalid CSRF'; 
            return; 
        }

        $userId = Session::get('pending_user_id');
        $userData = Session::get('pending_user_data');

        if (!$userId || !$userData) {
            $this->redirect('/login');
            return;
        }

        $code = $this->twoFactorService->initiateTwoFactor($userId, $userData['email']);
        
        $this->view('auth/verify-2fa', [
            'email' => $userData['email'],
            'success' => 'New verification code sent to your email'
        ]);
    }

    private function completeLogin(array $user): void
    {
        Auth::attempt($user);
        
        if ($user['role'] === 'admin') $this->redirect('/admin');
        if ($user['role'] === 'vendor') $this->redirect('/vendor');
        if ($user['role'] === 'driver') $this->redirect('/delivery/dashboard');
        if ($user['role'] === 'shelter') $this->redirect('/shelter/dashboard');
        $this->redirect('/consumer');
    }

    public function showRegister(): void
    {
        $this->view('auth/register');
    }

    public function register(): void
    {
        if (!CSRF::check($_POST['_csrf'] ?? '')) { 
            echo 'Invalid CSRF'; 
            return; 
        }
        
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

        // Send welcome email for consumers
        if ($role === 'consumer') {
            $this->sendWelcomeEmail($email, $name);
        }

        if ($role === 'vendor') {
            (new Vendor())->create($uid, []);
        }

        $this->view('auth/login', ['success' => 'Registration successful. Await approval if vendor.']);
    }

    public function showConsumerRegistration(): void
    {
        $this->view('auth/register-consumer');
    }

    public function registerConsumer(): void
    {
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

        // Send welcome email
        $this->sendWelcomeEmail($email, $name);

        $this->view('auth/login', ['success' => 'Registration successful! Please login.']);
    }

    private function sendWelcomeEmail(string $email, string $name): void
    {
        $subject = "Welcome to SaveEAT!";
        $body = "
            <h2>Welcome to SaveEAT, {$name}!</h2>
            <p>Thank you for joining our community dedicated to reducing food waste.</p>
            <p>With SaveEAT, you can:</p>
            <ul>
                <li>Discover discounted food from local vendors</li>
                <li>Save money while helping the environment</li>
                <li>Get quick deliveries to your location</li>
            </ul>
            <p>Start exploring today and make a difference!</p>
            <p>Best regards,<br>The SaveEAT Team</p>
        ";

        $mailer = new \App\Core\Mailer();
        $mailer->send($email, $subject, $body);
    }

    public function showVendorRegistration(): void
    {
        $this->view('auth/register-vendor');
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

        // Handle license file upload - FIXED
        $licensePath = null;
        if (isset($_FILES['license_file']) && $_FILES['license_file']['error'] === UPLOAD_ERR_OK) {
            $licensePath = \App\Core\Uploader::image($_FILES['license_file']);
        }

        // Create user
        $uid = $userModel->create([
            'name' => $contactName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'vendor',
            'status' => 'pending', // Vendors need approval
        ]);

        // Create vendor record - FIXED: Use correct field mapping
        $vendorModel = new Vendor();
        $vendorModel->create($uid, [
            'business_name' => $businessName,
            'address' => $address, // This gets mapped to 'location' in Vendor model
            'phone' => $phone,
        ]);

        // Create vendor verification record if license was uploaded
        if ($licensePath) {
            try {
                $verificationStmt = $vendorModel->getDb()->prepare("
                    INSERT INTO vendor_verifications (vendor_id, license_document_path, verification_status, created_at) 
                    VALUES (?, ?, 'pending', NOW())
                ");
                $verificationStmt->execute([$vendorModel->getDb()->lastInsertId(), $licensePath]);
            } catch (\Exception $e) {
                // Continue even if verification record fails
            }
        }

        $this->view('auth/login', ['success' => 'Vendor registration submitted! Please wait for approval before logging in.']);
    }

    public function showShelterRegistration(): void
    {
        $this->view('auth/register-shelter');
    }

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

        // Handle license file upload - FIXED
        $licensePath = null;
        if (isset($_FILES['license_file']) && $_FILES['license_file']['error'] === UPLOAD_ERR_OK) {
            $licensePath = \App\Core\Uploader::image($_FILES['license_file']);
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