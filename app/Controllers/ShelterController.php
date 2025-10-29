<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Shelter;
use App\Models\User;
use App\Models\Donation;
use App\Models\FoodItem;
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
        $donationModel = new Donation();
    
        $shelter = $shelterModel->findByUserId(Auth::id());
        
        // Check if shelter exists and get stats
        if ($shelter) {
            $stats = $shelterModel->getShelterStats($shelter['id']);
            $availableDonations = $shelterModel->getAvailableDonationsForShelter($shelter['id']);
        } else {
            $stats = ['pending_requests' => 0, 'completed_donations' => 0, 'total_items_received' => 0];
            $availableDonations = [];
        }
        
        $this->view('shelter/dashboard', [
            'shelter' => $shelter,
            'stats' => $stats,
            'availableDonations' => $availableDonations
        ]);
    }
public function donationRequests(): void
{
    Auth::requireRole(['shelter']);
    $shelterModel = new Shelter();
    $donationModel = new Donation();
    
    $shelter = $shelterModel->findByUserId(Auth::id());
    
    if (!$shelter) {
        Session::flash('error', 'Shelter not found');
        $this->redirect('/shelter/dashboard');
    }

    $availableDonations = $shelterModel->getAvailableDonationsForShelter($shelter['id']);
    $shelterDonations = $donationModel->getDonationsByShelter($shelter['id']);
    
    $this->view('shelter/donations', [
        'shelter' => $shelter,
        'availableDonations' => $availableDonations,
        'shelterDonations' => $shelterDonations
    ]);
}

    public function requestDonation(): void
    {
        Auth::requireRole(['shelter']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/shelter/donations');
        }

        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/shelter/donations');
        }

        $foodItemId = (int)($_POST['food_item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($foodItemId <= 0 || $quantity <= 0) {
            Session::flash('error', 'Invalid food item or quantity');
            $this->redirect('/shelter/donations');
        }

        try {
            $shelterModel = new Shelter();
            $foodModel = new FoodItem();
            $donationModel = new Donation();
            
            $shelter = $shelterModel->findByUserId(Auth::id());
            $foodItem = $foodModel->find($foodItemId);
            
            if (!$shelter || !$foodItem) {
                Session::flash('error', 'Shelter or food item not found');
                $this->redirect('/shelter/donations');
            }

            // Check if enough stock is available
            if ($foodItem['stock'] < $quantity) {
                Session::flash('error', 'Not enough stock available. Only ' . $foodItem['stock'] . ' portions left.');
                $this->redirect('/shelter/donations');
            }

            // Create donation request
            $donationId = $donationModel->create([
                'vendor_id' => $foodItem['vendor_id'],
                'shelter_id' => $shelter['id'],
                'food_item_id' => $foodItemId,
                'quantity' => $quantity,
                'donation_date' => date('Y-m-d'),
                'status' => 'pending',
                'notes' => $notes
            ]);

            // Update food item stock
            $foodModel->deductStock($foodItemId, $quantity);

            Session::flash('success', 'Donation request submitted successfully!');
            $this->redirect('/shelter/donations');

        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to request donation: ' . $e->getMessage());
            $this->redirect('/shelter/donations');
        }
    }

    public function donationHistory(): void
    {
        Auth::requireRole(['shelter']);
        $shelterModel = new Shelter();
        $donationModel = new Donation();
        
        $shelter = $shelterModel->findByUserId(Auth::id());
        
        if (!$shelter) {
            Session::flash('error', 'Shelter not found');
            $this->redirect('/shelter/dashboard');
        }

        $donations = $donationModel->getDonationsByShelter($shelter['id']);
        $stats = $shelterModel->getShelterStats($shelter['id']);
        
        $this->view('shelter/history', [
            'shelter' => $shelter,
            'donations' => $donations,
            'stats' => $stats
        ]);
    }

    public function settings(): void
    {
        Auth::requireRole(['shelter']);
        $shelterModel = new Shelter();
        $userModel = new User();
        
        $shelter = $shelterModel->findByUserId(Auth::id());
        $user = $userModel->findByEmail(Auth::user()['email']); // FIXED: Use findByEmail instead of find
        
        if (!$shelter || !$user) {
            Session::flash('error', 'Shelter or user not found');
            $this->redirect('/shelter/dashboard');
        }
        
        $this->view('shelter/settings', [
            'shelter' => $shelter,
            'user' => $user
        ]);
    }

    public function updateSettings(): void
    {
        Auth::requireRole(['shelter']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/shelter/settings');
        }

        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/shelter/settings');
        }

        $shelterName = trim($_POST['shelter_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $contactPerson = trim($_POST['contact_person'] ?? '');

        if (empty($shelterName) || empty($location) || empty($contactPhone)) {
            Session::flash('error', 'Shelter name, location, and contact phone are required');
            $this->redirect('/shelter/settings');
        }

        try {
            $shelterModel = new Shelter();
            $userModel = new User();
            
            $shelter = $shelterModel->findByUserId(Auth::id());
            
            if (!$shelter) {
                Session::flash('error', 'Shelter not found');
                $this->redirect('/shelter/settings');
            }

            // Update shelter
            $shelterModel->update($shelter['id'], [
                'shelter_name' => $shelterName,
                'location' => $location,
                'contact_phone' => $contactPhone,
                'capacity' => $capacity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Update user contact person name if provided
            if (!empty($contactPerson)) {
                $userModel->update(Auth::id(), [
                    'name' => $contactPerson
                ]);
            }

            Session::flash('success', 'Settings updated successfully!');
            $this->redirect('/shelter/settings');

        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to update settings: ' . $e->getMessage());
            $this->redirect('/shelter/settings');
        }
    }
}