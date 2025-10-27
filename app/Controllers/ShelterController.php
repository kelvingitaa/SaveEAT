<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Shelter;
use App\Models\User;
use App\Core\CSRF;

class ShelterController extends Controller
{
    public function register(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('/');
        }
        $this->view('auth/register-shelter');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/shelter/register');
        }

        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/shelter/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $shelterName = trim($_POST['shelter_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);

        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($shelterName) || empty($location)) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/shelter/register');
        }

        try {
            $userModel = new User();
            
            // Check if email exists
            if ($userModel->findByEmail($email)) {
                Session::flash('error', 'Email already exists');
                $this->redirect('/shelter/register');
            }

            // Create user
            $userId = $userModel->create([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'shelter',
                'status' => 'active'
            ]);

            // Create shelter
            $shelterModel = new Shelter();
            $db = $shelterModel->getDb();
            $stmt = $db->prepare("
                INSERT INTO shelters (user_id, shelter_name, location, contact_phone, capacity, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())
            ");
            $stmt->execute([$userId, $shelterName, $location, $contactPhone, $capacity]);

            Session::flash('success', 'Shelter registration submitted for verification');
            $this->redirect('/login');

        } catch (\Throwable $e) {
            Session::flash('error', 'Registration failed: ' . $e->getMessage());
            $this->redirect('/shelter/register');
        }
    }

    public function dashboard(): void
    {
        Auth::requireRole(['shelter']);
        $shelterModel = new Shelter();
        $shelter = $shelterModel->findByUserId(Auth::userId());
        
        $this->view('shelter/dashboard', [
            'shelter' => $shelter
        ]);
    }
}