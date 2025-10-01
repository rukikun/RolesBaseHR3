<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaimControllerSimple extends Controller
{
    public function index()
    {
        try {
            // Simple hardcoded employees for immediate functionality
            $employees = collect([
                (object) ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                (object) ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
                (object) ['id' => 3, 'first_name' => 'Mike', 'last_name' => 'Johnson'],
                (object) ['id' => 4, 'first_name' => 'Sarah', 'last_name' => 'Wilson'],
                (object) ['id' => 5, 'first_name' => 'Tom', 'last_name' => 'Brown']
            ]);
            
            // Simple hardcoded claim types
            $claimTypes = collect([
                (object) ['id' => 1, 'name' => 'Travel Expenses', 'code' => 'TRAVEL'],
                (object) ['id' => 2, 'name' => 'Meal Allowance', 'code' => 'MEAL'],
                (object) ['id' => 3, 'name' => 'Office Supplies', 'code' => 'OFFICE'],
                (object) ['id' => 4, 'name' => 'Training Costs', 'code' => 'TRAIN'],
                (object) ['id' => 5, 'name' => 'Medical Expenses', 'code' => 'MEDICAL']
            ]);
            
            // Get actual claims from database
            $claims = collect([]);
            try {
                // Try to get claims from database
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Ensure claims table exists
                $this->ensureClaimsTableExists($pdo);
                
                $stmt = $pdo->query("
                    SELECT 
                        c.*,
                        COALESCE(e.first_name, 'Unknown') as first_name,
                        COALESCE(e.last_name, 'Employee') as last_name,
                        CONCAT(COALESCE(e.first_name, 'Unknown'), ' ', COALESCE(e.last_name, 'Employee')) as employee_name,
                        COALESCE(ct.name, 'Unknown Type') as claim_type_name,
                        COALESCE(ct.code, 'N/A') as claim_type_code
                    FROM claims c
                    LEFT JOIN employees e ON c.employee_id = e.id
                    LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
                    ORDER BY c.created_at DESC
                ");
                $claims = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                \Log::info('Found ' . $claims->count() . ' claims in database');
            } catch (\Exception $e) {
                \Log::warning('Could not fetch claims from database: ' . $e->getMessage());
                $claims = collect([]);
            }
            
            // Calculate statistics from actual data
            $totalClaims = $claims->count();
            $pendingClaims = $claims->where('status', 'pending')->count();
            $approvedClaims = $claims->where('status', 'approved')->count();
            $totalAmount = $claims->whereIn('status', ['approved', 'paid'])->sum('amount');
            
            return view('claims_reimbursement', [
                'employees' => $employees,
                'claimTypes' => $claimTypes,
                'claims' => $claims,
                'totalClaims' => $totalClaims,
                'pendingClaims' => $pendingClaims,
                'approvedClaims' => $approvedClaims,
                'totalAmount' => $totalAmount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Claims error: ' . $e->getMessage());
            
            // Fallback with hardcoded data
            return view('claims_reimbursement', [
                'employees' => collect([
                    (object) ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                    (object) ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith']
                ]),
                'claimTypes' => collect([
                    (object) ['id' => 1, 'name' => 'Travel Expenses', 'code' => 'TRAVEL'],
                    (object) ['id' => 2, 'name' => 'Meal Allowance', 'code' => 'MEAL']
                ]),
                'claims' => collect([]),
                'totalClaims' => 0,
                'pendingClaims' => 0,
                'approvedClaims' => 0,
                'totalAmount' => 0
            ]);
        }
    }
    
    public function store(Request $request)
    {
        try {
            \Log::info('=== ClaimControllerSimple store method called ===');
            \Log::info('SUCCESS! The correct controller is being used!');
            \Log::info('Request data: ' . json_encode($request->all()));
            
            // Simple validation - redirect back with errors
            if (!$request->employee_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please select an employee.');
            }
            
            if (!$request->claim_type_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please select a claim type.');
            }
            
            if (!$request->amount || $request->amount <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please enter a valid amount.');
            }
            
            if (!$request->claim_date) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please select a claim date.');
            }
            
            if (!$request->description) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please enter a description.');
            }
            
            // All validation passed - save to database
            \Log::info('Claim validation passed, saving to database');
            
            try {
                // Save to database using PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Ensure claims table exists
                $this->ensureClaimsTableExists($pdo);
                
                // Handle file upload if present
                $receiptPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $receiptPath = $file->storeAs('receipts', $filename, 'public');
                    \Log::info('File uploaded: ' . $receiptPath);
                }
                
                // Insert claim into database
                $stmt = $pdo->prepare("
                    INSERT INTO claims (
                        employee_id, claim_type_id, amount, claim_date, 
                        description, receipt_path, status, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
                ");
                
                $stmt->execute([
                    $request->employee_id,
                    $request->claim_type_id,
                    $request->amount,
                    $request->claim_date,
                    $request->description,
                    $receiptPath
                ]);
                
                $claimId = $pdo->lastInsertId();
                \Log::info('Claim saved successfully with ID: ' . $claimId);
                
                // Always redirect with success message for consistent UI
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim submitted successfully!');
                
            } catch (\Exception $dbError) {
                \Log::error('Database error: ' . $dbError->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error saving claim to database: ' . $dbError->getMessage());
            }
            
        } catch (\Exception $e) {
            \Log::error('Claim store error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error processing claim: ' . $e->getMessage());
        }
    }
    
    public function storeClaimTypeWeb(Request $request)
    {
        try {
            // Simple validation
            if (!$request->name) {
                return redirect()->back()->with('error', 'Claim type name is required.');
            }
            
            if (!$request->code) {
                return redirect()->back()->with('error', 'Claim type code is required.');
            }
            
            // For now, just return success without saving
            return redirect()->route('claims-reimbursement')
                ->with('success', 'Claim type created successfully! (Test mode - not saved to database)');
                
        } catch (\Exception $e) {
            \Log::error('Claim type store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating claim type: ' . $e->getMessage());
        }
    }
    
    // Add JSON response for claim type store (for AJAX calls)
    public function storeClaimType(Request $request)
    {
        try {
            // Simple validation
            if (!$request->name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim type name is required.'
                ], 400);
            }
            
            if (!$request->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim type code is required.'
                ], 400);
            }
            
            // For now, just return success without saving
            return response()->json([
                'success' => true,
                'message' => 'Claim type created successfully! (Test mode - not saved to database)'
            ]);
                
        } catch (\Exception $e) {
            \Log::error('Claim type store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating claim type: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Test method to verify the controller is working
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'ClaimControllerSimple is working correctly!',
            'controller' => 'ClaimControllerSimple',
            'timestamp' => now()
        ]);
    }
    
    // Ensure claims table exists
    private function ensureClaimsTableExists($pdo)
    {
        try {
            // Check if claims table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'claims'");
            if ($stmt->rowCount() == 0) {
                // Create claims table
                $pdo->exec("
                    CREATE TABLE claims (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        employee_id INT NOT NULL,
                        claim_type_id INT NOT NULL,
                        amount DECIMAL(10,2) NOT NULL,
                        claim_date DATE NOT NULL,
                        description TEXT NOT NULL,
                        receipt_path VARCHAR(255) NULL,
                        status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
                        approved_by INT NULL,
                        approved_at TIMESTAMP NULL,
                        rejection_reason TEXT NULL,
                        paid_at TIMESTAMP NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                \Log::info('Claims table created successfully');
            }
        } catch (\Exception $e) {
            \Log::warning('Could not create claims table: ' . $e->getMessage());
        }
    }

    /**
     * Show claim details for admin API
     */
    public function show($id)
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("
                SELECT c.*, 
                       CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) as employee_name,
                       ct.name as claim_type_name,
                       CONCAT(COALESCE(approver.first_name, ''), ' ', COALESCE(approver.last_name, '')) as approved_by_name
                FROM claims c
                LEFT JOIN employees e ON c.employee_id = e.id
                LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
                LEFT JOIN employees approver ON c.approved_by = approver.id
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $claim = $stmt->fetch(\PDO::FETCH_OBJ);

            if (!$claim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'claim' => $claim
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ClaimControllerSimple@show: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve claim via API
     */
    public function approve($id)
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Check if claim exists and is pending
            $stmt = $pdo->prepare("SELECT * FROM claims WHERE id = ?");
            $stmt->execute([$id]);
            $claim = $stmt->fetch(\PDO::FETCH_OBJ);

            if (!$claim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim not found'
                ], 404);
            }

            if ($claim->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim has already been processed'
                ], 400);
            }

            // Update claim status
            $stmt = $pdo->prepare("
                UPDATE claims 
                SET status = 'approved', 
                    approved_by = ?, 
                    approved_at = NOW(), 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([1, $id]); // Default admin ID

            return response()->json([
                'success' => true,
                'message' => 'Claim approved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ClaimControllerSimple@approve: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject claim via API
     */
    public function reject($id)
    {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Check if claim exists and is pending
            $stmt = $pdo->prepare("SELECT * FROM claims WHERE id = ?");
            $stmt->execute([$id]);
            $claim = $stmt->fetch(\PDO::FETCH_OBJ);

            if (!$claim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim not found'
                ], 404);
            }

            if ($claim->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Claim has already been processed'
                ], 400);
            }

            // Update claim status
            $stmt = $pdo->prepare("
                UPDATE claims 
                SET status = 'rejected', 
                    approved_by = ?, 
                    approved_at = NOW(), 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([1, $id]); // Default admin ID

            return response()->json([
                'success' => true,
                'message' => 'Claim rejected successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in ClaimControllerSimple@reject: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject claim: ' . $e->getMessage()
            ], 500);
        }
    }
}
