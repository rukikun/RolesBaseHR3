<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Employee;
use Carbon\Carbon;

class ClaimsReimbursementController extends Controller
{
    /**
     * Display claims reimbursement dashboard with proper MVC structure
     */
    public function index()
    {
        try {
            // Use Eloquent models with fallback to raw queries
            $claimTypes = collect([]);
            $employees = collect([]);
            $claims = collect([]);
            
            try {
                // Try using Eloquent first
                $claimTypes = ClaimType::where('is_active', true)->orderBy('name')->get();
                $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
                $claims = Claim::with(['employee', 'claimType', 'approver'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($claim) {
                        // Add computed properties for blade compatibility
                        $claim->employee_name = ($claim->employee->first_name ?? 'Employee') . ' ' . ($claim->employee->last_name ?? 'Unknown');
                        $claim->claim_type_name = $claim->claimType->name ?? 'Unknown Type';
                        $claim->claim_type_code = $claim->claimType->code ?? 'N/A';
                        return $claim;
                    });
                
                Log::info('Eloquent - Retrieved ' . $claimTypes->count() . ' claim types, ' . $claims->count() . ' claims');
            } catch (\Exception $e) {
                Log::warning('Eloquent failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw PDO queries with table creation
                try {
                    $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Auto-create claim_types table if not exists
                    $pdo->exec("CREATE TABLE IF NOT EXISTS claim_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        code VARCHAR(10) NOT NULL,
                        description TEXT,
                        max_amount DECIMAL(10,2) DEFAULT NULL,
                        requires_attachment BOOLEAN DEFAULT TRUE,
                        auto_approve BOOLEAN DEFAULT FALSE,
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_name_code (name, code)
                    )");
                    
                    // Auto-create claims table if not exists
                    $pdo->exec("CREATE TABLE IF NOT EXISTS claims (
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
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                        FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE,
                        FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
                    )");
                    
                    // Insert sample claim types if table is empty
                    $stmt = $pdo->query("SELECT COUNT(*) FROM claim_types");
                    if ($stmt->fetchColumn() == 0) {
                        $pdo->exec("INSERT IGNORE INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active) VALUES
                            ('Travel Expenses', 'TRAVEL', 'Business travel and transportation costs', 1000.00, TRUE, FALSE, TRUE),
                            ('Meal Allowance', 'MEAL', 'Business meal expenses', 100.00, TRUE, TRUE, TRUE),
                            ('Office Supplies', 'OFFICE', 'Office equipment and supplies', 500.00, TRUE, FALSE, TRUE),
                            ('Training Costs', 'TRAIN', 'Professional development and training', 2000.00, TRUE, FALSE, TRUE),
                            ('Medical Expenses', 'MEDICAL', 'Medical reimbursements', 1500.00, TRUE, FALSE, TRUE)");
                    }
                    
                    // Get claim types
                    $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 ORDER BY name");
                    $claimTypesData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $claimTypes = collect($claimTypesData);
                    
                    // Get employees
                    $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
                    $employeesData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $employees = collect($employeesData);
                    
                    // Get ALL claims with joins
                    $stmt = $pdo->query("
                        SELECT c.id, c.employee_id, c.claim_type_id, c.amount, c.claim_date, 
                               c.description, c.receipt_path, c.status, c.approved_by, c.approved_at,
                               c.created_at, c.updated_at,
                               COALESCE(e.first_name, 'Employee') as first_name, 
                               COALESCE(e.last_name, CONCAT('ID:', c.employee_id)) as last_name,
                               CONCAT(COALESCE(e.first_name, 'Employee'), ' ', COALESCE(e.last_name, CONCAT('ID:', c.employee_id))) as employee_name,
                               COALESCE(ct.name, CONCAT('Type ID:', c.claim_type_id)) as claim_type_name, 
                               COALESCE(ct.code, 'N/A') as claim_type_code
                        FROM claims c
                        LEFT JOIN employees e ON c.employee_id = e.id
                        LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
                        ORDER BY c.created_at DESC
                    ");
                    $claimsData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $claims = collect($claimsData);
                    
                    Log::info('Raw PDO - Retrieved ' . count($claimTypesData) . ' claim types, ' . count($claimsData) . ' claims');
                } catch (\Exception $e2) {
                    Log::error('Raw PDO also failed: ' . $e2->getMessage());
                    // Final fallback with empty collections
                    $claimTypes = collect([]);
                    $employees = collect([]);
                    $claims = collect([]);
                }
            }
            
            // Calculate statistics for dashboard cards
            $totalClaims = $claims->count();
            $pendingClaims = $claims->where('status', 'pending')->count();
            $approvedClaims = $claims->where('status', 'approved')->count();
            $totalAmount = $claims->whereIn('status', ['approved', 'paid'])->sum('amount');
                
            return view('claims.reimbursement', compact(
                'claimTypes', 'employees', 'claims', 
                'totalClaims', 'pendingClaims', 'approvedClaims', 'totalAmount'
            ));
            
        } catch (\Exception $e) {
            // Final fallback with empty collections
            Log::error('Claims index error: ' . $e->getMessage());
            
            return view('claims.reimbursement', [
                'claimTypes' => collect([]),
                'employees' => collect([]),
                'claims' => collect([]),
                'totalClaims' => 0,
                'pendingClaims' => 0,
                'approvedClaims' => 0,
                'totalAmount' => 0
            ])->with('error', 'Error loading claims data: ' . $e->getMessage());
        }
    }

    /**
     * Store new claim with proper validation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'claim_type_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'claim_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->route('claims-reimbursement')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Handle file upload
            $receiptPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $receiptPath = $file->storeAs('receipts', $filename, 'public');
            }

            // Try Eloquent first
            try {
                $claim = Claim::create([
                    'employee_id' => $request->employee_id,
                    'claim_type_id' => $request->claim_type_id,
                    'amount' => $request->amount,
                    'claim_date' => $request->claim_date,
                    'description' => $request->description,
                    'receipt_path' => $receiptPath,
                    'status' => 'pending'
                ]);

                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim submitted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, receipt_path, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
                ");
                $stmt->execute([
                    $request->employee_id,
                    $request->claim_type_id,
                    $request->amount,
                    $request->claim_date,
                    $request->description,
                    $receiptPath
                ]);
                
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim submitted successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim store error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')
                ->with('error', 'Error creating claim: ' . $e->getMessage());
        }
    }

    /**
     * Store new claim type
     */
    public function storeClaimType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
            'max_amount' => 'nullable|numeric|min:0',
            'requires_attachment' => 'boolean',
            'auto_approve' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->route('claims-reimbursement')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Try Eloquent first
            try {
                $claimType = ClaimType::create([
                    'name' => $request->name,
                    'code' => strtoupper($request->code),
                    'description' => $request->description,
                    'max_amount' => $request->max_amount,
                    'requires_attachment' => $request->boolean('requires_attachment', true),
                    'auto_approve' => $request->boolean('auto_approve', false),
                    'is_active' => true
                ]);

                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim type created successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    INSERT INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
                ");
                $stmt->execute([
                    $request->name,
                    strtoupper($request->code),
                    $request->description,
                    $request->max_amount,
                    $request->has('requires_attachment') ? 1 : 0,
                    $request->has('auto_approve') ? 1 : 0
                ]);
                
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim type created successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim type store error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')
                ->with('error', 'Error creating claim type: ' . $e->getMessage());
        }
    }

    /**
     * Approve claim
     */
    public function approve($id)
    {
        try {
            // Try Eloquent first
            try {
                $claim = Claim::findOrFail($id);
                
                if ($claim->status !== 'pending') {
                    return redirect()->route('claims-reimbursement')
                        ->with('error', 'Only pending claims can be approved.');
                }

                $claim->update([
                    'status' => 'approved',
                    'approved_by' => 1, // Default admin ID
                    'approved_at' => now()
                ]);

                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim approved successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE claims 
                    SET status = 'approved', approved_by = 1, approved_at = NOW(), updated_at = NOW() 
                    WHERE id = ? AND status = 'pending'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim approved successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim approve error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')
                ->with('error', 'Error approving claim: ' . $e->getMessage());
        }
    }

    /**
     * Reject claim
     */
    public function reject($id)
    {
        try {
            // Try Eloquent first
            try {
                $claim = Claim::findOrFail($id);
                
                if ($claim->status !== 'pending') {
                    return redirect()->route('claims-reimbursement')
                        ->with('error', 'Only pending claims can be rejected.');
                }

                $claim->update([
                    'status' => 'rejected',
                    'approved_by' => 1, // Default admin ID
                    'approved_at' => now()
                ]);

                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim rejected successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE claims 
                    SET status = 'rejected', approved_by = 1, approved_at = NOW(), updated_at = NOW() 
                    WHERE id = ? AND status = 'pending'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim rejected successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim reject error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')
                ->with('error', 'Error rejecting claim: ' . $e->getMessage());
        }
    }

    /**
     * Mark claim as paid
     */
    public function markAsPaid($id)
    {
        try {
            // Try Eloquent first
            try {
                $claim = Claim::findOrFail($id);
                
                if ($claim->status !== 'approved') {
                    return redirect()->route('claims-reimbursement')
                        ->with('error', 'Only approved claims can be marked as paid.');
                }

                $claim->update([
                    'status' => 'paid',
                    'updated_at' => now()
                ]);

                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim marked as paid successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE claims 
                    SET status = 'paid', updated_at = NOW() 
                    WHERE id = ? AND status = 'approved'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim marked as paid successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim pay error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')
                ->with('error', 'Error marking claim as paid: ' . $e->getMessage());
        }
    }

    /**
     * Delete claim type
     */
    public function deleteClaimType($id)
    {
        try {
            // Try Eloquent first
            try {
                $claimType = ClaimType::findOrFail($id);
                $claimType->delete();

                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim type deleted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("DELETE FROM claim_types WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('claims-reimbursement')
                    ->with('success', 'Claim type deleted successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim type delete error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')
                ->with('error', 'Error deleting claim type: ' . $e->getMessage());
        }
    }
}
