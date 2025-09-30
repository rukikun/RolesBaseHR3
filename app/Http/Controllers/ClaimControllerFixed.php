<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ClaimControllerFixed extends Controller
{
    public function index()
    {
        try {
            // Set database connection to hr3systemdb
            config(['database.connections.mysql.database' => 'hr3systemdb']);
            DB::purge('mysql');
            
            // Initialize collections
            $claimTypes = collect([]);
            $employees = collect([]);
            $claims = collect([]);
            
            // Create database connection
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Ensure tables exist
            $this->ensureTablesExist($pdo);
            
            // Get employees - try Eloquent first, then PDO fallback
            try {
                $employees = Employee::where('status', 'active')
                    ->orderBy('first_name')
                    ->get();
                    
                if ($employees->count() == 0) {
                    // Create sample employees if none exist
                    $this->createSampleEmployees($pdo);
                    $employees = Employee::where('status', 'active')
                        ->orderBy('first_name')
                        ->get();
                }
            } catch (\Exception $e) {
                // Fallback to PDO
                $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
                $employees = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                
                if ($employees->count() == 0) {
                    $this->createSampleEmployees($pdo);
                    $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
                    $employees = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                }
            }
            
            // Get claim types
            try {
                $claimTypes = ClaimType::where('is_active', true)
                    ->orderBy('name')
                    ->get();
                    
                if ($claimTypes->count() == 0) {
                    $this->createSampleClaimTypes($pdo);
                    $claimTypes = ClaimType::where('is_active', true)
                        ->orderBy('name')
                        ->get();
                }
            } catch (\Exception $e) {
                // Fallback to PDO
                $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 ORDER BY name");
                $claimTypes = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                
                if ($claimTypes->count() == 0) {
                    $this->createSampleClaimTypes($pdo);
                    $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 ORDER BY name");
                    $claimTypes = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                }
            }
            
            // Get claims with relationships
            try {
                $claims = Claim::with(['employee', 'claimType'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($claim) {
                        $claim->employee_name = $claim->employee 
                            ? $claim->employee->first_name . ' ' . $claim->employee->last_name 
                            : 'Unknown Employee';
                        $claim->claim_type_name = $claim->claimType 
                            ? $claim->claimType->name 
                            : 'Unknown Type';
                        return $claim;
                    });
            } catch (\Exception $e) {
                // Fallback to PDO
                $stmt = $pdo->query("
                    SELECT 
                        c.*,
                        CONCAT(COALESCE(e.first_name, 'Unknown'), ' ', COALESCE(e.last_name, 'Employee')) as employee_name,
                        COALESCE(ct.name, 'Unknown Type') as claim_type_name
                    FROM claims c
                    LEFT JOIN employees e ON c.employee_id = e.id
                    LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
                    ORDER BY c.created_at DESC
                ");
                $claims = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
            }
            
            // Calculate statistics
            $totalClaims = $claims->count();
            $pendingClaims = $claims->where('status', 'pending')->count();
            $approvedClaims = $claims->where('status', 'approved')->count();
            $totalAmount = $claims->where('status', 'approved')->sum('amount');
            
            return view('claims_reimbursement', compact(
                'claimTypes', 
                'employees', 
                'claims',
                'totalClaims',
                'pendingClaims', 
                'approvedClaims',
                'totalAmount'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Claims index error: ' . $e->getMessage());
            
            // Return with empty collections but ensure employees fallback
            return view('claims_reimbursement', [
                'claimTypes' => collect([]),
                'employees' => $this->getFallbackEmployees(),
                'claims' => collect([]),
                'totalClaims' => 0,
                'pendingClaims' => 0,
                'approvedClaims' => 0,
                'totalAmount' => 0
            ]);
        }
    }
    
    private function getFallbackEmployees()
    {
        // Create fallback employee objects
        return collect([
            (object) ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
            (object) ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
            (object) ['id' => 3, 'first_name' => 'Mike', 'last_name' => 'Johnson'],
            (object) ['id' => 4, 'first_name' => 'Sarah', 'last_name' => 'Wilson'],
            (object) ['id' => 5, 'first_name' => 'Tom', 'last_name' => 'Brown']
        ]);
    }
    
    private function ensureTablesExist($pdo)
    {
        // Create employees table
        $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE,
            phone VARCHAR(20),
            position VARCHAR(100),
            department VARCHAR(100),
            hire_date DATE,
            salary DECIMAL(10,2),
            status ENUM('active', 'inactive') DEFAULT 'active',
            online_status ENUM('online', 'offline') DEFAULT 'offline',
            last_activity TIMESTAMP NULL,
            password VARCHAR(255),
            profile_picture VARCHAR(255),
            remember_token VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Create claim_types table
        $pdo->exec("CREATE TABLE IF NOT EXISTS claim_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(10) NOT NULL UNIQUE,
            description TEXT,
            max_amount DECIMAL(10,2),
            requires_attachment BOOLEAN DEFAULT TRUE,
            auto_approve BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Create claims table
        $pdo->exec("CREATE TABLE IF NOT EXISTS claims (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            claim_type_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            claim_date DATE NOT NULL,
            description TEXT,
            receipt_path VARCHAR(255),
            attachment_path VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
            FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE
        )");
    }
    
    private function createSampleEmployees($pdo)
    {
        $employees = [
            ['John', 'Doe', 'john.doe@jetlouge.com', '555-0101', 'Software Developer', 'IT', '2023-01-15', 75000],
            ['Jane', 'Smith', 'jane.smith@jetlouge.com', '555-0102', 'Project Manager', 'IT', '2022-03-20', 85000],
            ['Mike', 'Johnson', 'mike.johnson@jetlouge.com', '555-0103', 'HR Specialist', 'HR', '2023-06-10', 65000],
            ['Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '555-0104', 'Accountant', 'Finance', '2022-11-05', 70000],
            ['Tom', 'Brown', 'tom.brown@jetlouge.com', '555-0105', 'Sales Representative', 'Sales', '2023-02-28', 60000]
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        
        foreach ($employees as $emp) {
            $stmt->execute($emp);
        }
    }
    
    private function createSampleClaimTypes($pdo)
    {
        $claimTypes = [
            ['Travel Expenses', 'TRAVEL', 'Business travel and accommodation expenses', 2000.00, 1, 0],
            ['Meal Allowance', 'MEAL', 'Daily meal allowances and business meals', 100.00, 0, 1],
            ['Office Supplies', 'OFFICE', 'Office equipment and supplies', 500.00, 1, 0],
            ['Training Costs', 'TRAIN', 'Professional development and training', 1500.00, 1, 0],
            ['Medical Expenses', 'MEDICAL', 'Medical and health-related expenses', 1000.00, 1, 0]
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        
        foreach ($claimTypes as $type) {
            $stmt->execute($type);
        }
    }
    
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer|min:1',
                'claim_type_id' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:0.01',
                'claim_date' => 'required|date',
                'description' => 'required|string|max:1000',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Set database connection
            config(['database.connections.mysql.database' => 'hr3systemdb']);
            DB::purge('mysql');
            
            // Verify employee exists
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT id FROM employees WHERE id = ? AND status = 'active'");
            $stmt->execute([$request->employee_id]);
            $employee = $stmt->fetch();
            
            if (!$employee) {
                return redirect()->back()
                    ->with('error', 'Selected employee not found or inactive.')
                    ->withInput();
            }
            
            // Verify claim type exists
            $stmt = $pdo->prepare("SELECT id FROM claim_types WHERE id = ? AND is_active = 1");
            $stmt->execute([$request->claim_type_id]);
            $claimType = $stmt->fetch();
            
            if (!$claimType) {
                return redirect()->back()
                    ->with('error', 'Selected claim type not found or inactive.')
                    ->withInput();
            }
            
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('claims/attachments', $filename, 'public');
            }
            
            // Create claim using direct database insertion
            $stmt = $pdo->prepare("
                INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, attachment_path, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
            ");
            
            $stmt->execute([
                $request->employee_id,
                $request->claim_type_id,
                $request->amount,
                $request->claim_date,
                $request->description,
                $attachmentPath
            ]);
            
            return redirect()->route('claims-reimbursement')
                ->with('success', 'Claim submitted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Claim creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating claim: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    // Claim Type Management
    public function storeClaimTypeWeb(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:10|unique:claim_types,code',
                'description' => 'nullable|string',
                'max_amount' => 'nullable|numeric|min:0',
                'requires_attachment' => 'boolean',
                'auto_approve' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Set database connection
            config(['database.connections.mysql.database' => 'hr3systemdb']);
            DB::purge('mysql');
            
            $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("
                INSERT INTO claim_types (name, code, description, max_amount, requires_attachment, auto_approve, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            
            $stmt->execute([
                $request->name,
                $request->code,
                $request->description,
                $request->max_amount,
                $request->has('requires_attachment') ? 1 : 0,
                $request->has('auto_approve') ? 1 : 0
            ]);
            
            return redirect()->route('claims-reimbursement')
                ->with('success', 'Claim type created successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Claim type creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error creating claim type: ' . $e->getMessage())
                ->withInput();
        }
    }
}
