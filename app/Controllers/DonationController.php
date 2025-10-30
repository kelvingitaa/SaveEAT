<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Donation;
use App\Models\FoodItem;
use App\Models\Shelter;
use App\Models\Vendor;
use App\Core\CSRF;
use PDO;

class DonationController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $donationModel = new Donation();
        $foodModel = new FoodItem();
        
        $vendor = $vendorModel->findByUserId(Auth::id());
        $donations = $donationModel->getDonationsByVendor($vendor['id']);
        $expiringItems = $foodModel->getExpiringItems($vendor['id']);
        
        $this->view('vendor/donation', [
            'vendor' => $vendor,
            'donations' => $donations,
            'expiringItems' => $expiringItems
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $shelterModel = new Shelter();
        $foodModel = new FoodItem();
        
        $vendor = $vendorModel->findByUserId(Auth::id());
        $shelters = $shelterModel->getActiveShelters();
        $availableItems = $foodModel->getAvailableItemsForDonation($vendor['id']);
        
        $this->view('donation/create', [
            'vendor' => $vendor,
            'shelters' => $shelters,
            'availableItems' => $availableItems
        ]);
    }

    public function store(): void
    {
        Auth::requireRole(['vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/donations');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/donations/create');
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId(Auth::id());
        
        $foodItemId = (int)($_POST['food_item_id'] ?? 0);
        $shelterId = (int)($_POST['shelter_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $donationDate = $_POST['donation_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');
        
        // Validation
        if ($foodItemId <= 0 || $shelterId <= 0 || $quantity <= 0) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/donations/create');
        }
        
        try {
            $donationModel = new Donation();
            
            // Create donation
            $donationId = $donationModel->create([
                'vendor_id' => $vendor['id'],
                'shelter_id' => $shelterId,
                'food_item_id' => $foodItemId,
                'quantity' => $quantity,
                'donation_date' => $donationDate,
                'status' => 'scheduled',
                'notes' => $notes
            ]);
            
            // Update food item stock
            $foodModel = new FoodItem();
            $foodModel->deductStock($foodItemId, $quantity);
            
            Session::flash('success', 'Donation scheduled successfully!');
            $this->redirect('/donations');
            
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to create donation: ' . $e->getMessage());
            $this->redirect('/donations/create');
        }
    }

    public function shelterRequests(): void
    {
        Auth::requireRole(['shelter']);
        
        $shelterModel = new Shelter();
        $donationModel = new Donation();
        
        $shelter = $shelterModel->findByUserId(Auth::id());
        $donations = $donationModel->getDonationsByShelter($shelter['id']);
        $availableDonations = $donationModel->getAvailableDonations();
        
        $this->view('donation/requests', [
            'shelter' => $shelter,
            'donations' => $donations,
            'availableDonations' => $availableDonations
        ]);
    }

    public function updateStatus(): void
    {
        Auth::requireRole(['vendor', 'admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/donations');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/donations');
        }
        
        $donationId = (int)($_POST['donation_id'] ?? 0);
        $status = $_POST['status'] ?? 'scheduled';
        
        try {
            $donationModel = new Donation();
            $success = $donationModel->updateStatus($donationId, $status);
            
            if ($success) {
                Session::flash('success', 'Donation status updated');
            } else {
                Session::flash('error', 'Failed to update donation status');
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to update donation status: ' . $e->getMessage());
        }
        
        $this->redirect('/donations');
    }

    public function adminIndex(): void
    {
        Auth::requireRole(['admin']);
        $donationModel = new Donation();
        $db = $donationModel->getDb();
        
        try {
            $donations = $db->query("
                SELECT d.*, v.business_name, s.shelter_name, u.name as requester_name
                FROM donations d 
                LEFT JOIN vendors v ON d.vendor_id = v.id 
                LEFT JOIN shelters s ON d.shelter_id = s.id 
                LEFT JOIN users u ON s.user_id = u.id 
                ORDER BY d.created_at DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Throwable $e) {
            http_response_code(500);
            Session::flash('error', 'Failed to load donations: ' . $e->getMessage());
            $donations = [];
        }
        
        $this->view('admin/donations', ['donations' => $donations]);
    }
}