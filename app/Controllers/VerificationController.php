<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Vendor;
use App\Models\VendorVerification;
use App\Core\CSRF;

class VerificationController extends Controller
{
    public function uploadLicense(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $verificationModel = new VendorVerification();
        
        $vendor = $vendorModel->findByUserId(Auth::userId());
        $verification = $verificationModel->findByVendorId($vendor['id']);
        
        $this->view('verification/upload-license', [
            'vendor' => $vendor,
            'verification' => $verification
        ]);
    }

    public function processLicense(): void
    {
        Auth::requireRole(['vendor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/verification/upload-license');
        }
        
        $token = $_POST['_csrf'] ?? null;
        if (!$token || !CSRF::check($token)) {
            Session::flash('error', 'Invalid CSRF token');
            $this->redirect('/verification/upload-license');
        }
        
        $vendorModel = new Vendor();
        $vendor = $vendorModel->findByUserId(Auth::userId());
        
        if (!isset($_FILES['license_document']) || $_FILES['license_document']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid file to upload');
            $this->redirect('/verification/upload-license');
        }
        
        try {
            $verificationModel = new VendorVerification();
            $success = $verificationModel->saveLicenseDocument($vendor['id'], $_FILES['license_document']);
            
            if ($success) {
                Session::flash('success', 'License document uploaded successfully! Your verification is pending review.');
            } else {
                Session::flash('error', 'Failed to upload document. Please try again.');
            }
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }
        
        $this->redirect('/verification/upload-license');
    }

    public function status(): void
    {
        Auth::requireRole(['vendor']);
        
        $vendorModel = new Vendor();
        $verificationModel = new VendorVerification();
        
        $vendor = $vendorModel->findByUserId(Auth::userId());
        $verification = $verificationModel->findByVendorId($vendor['id']);
        
        $this->view('verification/status', [
            'vendor' => $vendor,
            'verification' => $verification
        ]);
    }
}