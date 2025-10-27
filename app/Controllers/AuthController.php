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
        // redirect by role
        if ($u['role'] === 'admin') $this->redirect('/admin');
        if ($u['role'] === 'vendor') $this->redirect('/vendor');
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

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
