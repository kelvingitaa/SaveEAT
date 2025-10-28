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

class DonationController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $donationModel = new Donation();
        $foodModel = new FoodItem();
        
        // FIXED: Auth::userId() → Auth::id()
        $vendor = $vendorModel->findByUserId(Auth::id());
        $donations = $donationModel->getDonationsByVendor($vendor['id']);
        $expiringItems = $foodModel->getExpiringItems($vendor['id']);
        
        // FIXED: donation/index → vendor/donation
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
        
        // FIXED: Auth::userId() → Auth::id()
        $vendor = $vendorModel->findByUserId(Auth::id());
        $shelters = $shelterModel->getActiveShelters();
        $availableItems = $foodModel->getAvailableItemsForDonation($vendor['id']);
        
        // FIXED: donation/create → donation/create (check if this exists in vendor folder)
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
        // FIXED: Auth::userId() → Auth::id()
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
        
        // FIXED: Auth::userId() → Auth::id()
        $shelter = $shelterModel->findByUserId(Auth::id());
        $donations = $donationModel->getDonationsByShelter($shelter['id']);
        $availableDonations = $donationModel->getAvailableDonations();
        
        // FIXED: donation/shelter-requests → donation/requests
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
        $stats = $donationModel->getDonationStats();
        $recentDonations = $donationModel->getRecentDonations();
        
        $this->view('admin/donations', [
            'stats' => $stats,
            'recentDonations' => $recentDonations
        ]);
    }
}