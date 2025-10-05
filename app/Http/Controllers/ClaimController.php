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

class ClaimController extends Controller
{
    public function index()
    {
        try {
            // Use Eloquent models with fallback to raw queries
            $claimTypes = collect([]);
            $employees = collect([]);
            $claims = collect([]);
            
            // Clear edit session data if not explicitly showing modal
            if (!session('show_edit_modal')) {
                session()->forget(['edit_claim', 'show_edit_modal']);
            }
            
            // TEMPORARY: Skip Eloquent and use PDO directly for debugging
            \Log::info('Skipping Eloquent, using PDO directly for debugging...');
            
            // Fallback to raw PDO queries with table creation
            try {
                    $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // TEMPORARILY DISABLED: Auto-create tables if they don't exist
                    // $this->createClaimTables($pdo);
                    \Log::info('Skipping table creation - tables should already exist');
                    
                    // Get claim types
                    \Log::info('Querying claim_types...');
                
                    // First check if table exists and what columns it has
                    $tableInfo = $pdo->query("DESCRIBE claim_types")->fetchAll(\PDO::FETCH_ASSOC);
                    \Log::info('claim_types table structure: ' . json_encode($tableInfo));
                
                    // Try different query variations
                    try {
                        $stmt = $pdo->query("SELECT * FROM claim_types WHERE is_active = 1 ORDER BY name");
                    } catch (\Exception $e) {
                        \Log::warning('Query with is_active failed: ' . $e->getMessage());
                        // Try without is_active filter
                        $stmt = $pdo->query("SELECT * FROM claim_types ORDER BY name");
                    }
                
                    $claimTypes = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                    \Log::info('Found ' . $claimTypes->count() . ' claim types');
                
                    if ($claimTypes->count() > 0) {
                        \Log::info('First claim type: ' . json_encode($claimTypes->first()));
                    }
                    
                    // Get employees using Eloquent
                    \Log::info('Querying employees with Eloquent...');
                    try {
                        $employees = Employee::where('status', 'active')
                            ->orderBy('first_name')
                            ->get();
                        \Log::info('Eloquent employees query successful: ' . $employees->count() . ' employees found');
                        
                        // If no employees found, create some sample employees
                        if ($employees->count() == 0) {
                            \Log::info('No employees found, creating sample employees...');
                            $this->createSampleEmployees($pdo);
                            
                            // Re-query employees
                            $employees = Employee::where('status', 'active')
                                ->orderBy('first_name')
                                ->get();
                            \Log::info('After creating samples, found ' . $employees->count() . ' employees');
                        }
                    } catch (\Exception $eloquentError) {
                        \Log::warning('Eloquent employees query failed, falling back to PDO: ' . $eloquentError->getMessage());
                        // Fallback to PDO query
                        $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
                        $employees = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                        \Log::info('PDO fallback - Found ' . $employees->count() . ' employees');
                        
                        if ($employees->count() == 0) {
                            \Log::info('No employees found, creating sample employees...');
                            $this->createSampleEmployees($pdo);
                            
                            // Re-query employees
                            $stmt = $pdo->query("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name");
                            $employees = collect($stmt->fetchAll(\PDO::FETCH_OBJ));
                            \Log::info('After creating samples, found ' . $employees->count() . ' employees');
                        }
                    }
                    
                    // Get claims using Eloquent with relationships
                    \Log::info('Querying claims with Eloquent...');
                    try {
                        $claims = Claim::with(['employee', 'claimType', 'approver'])
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(function ($claim) {
                                // Add computed properties for blade compatibility
                                $claim->employee_name = $claim->employee 
                                    ? $claim->employee->first_name . ' ' . $claim->employee->last_name 
                                    : 'Unknown Employee';
                                $claim->claim_type_name = $claim->claimType 
                                    ? $claim->claimType->name 
                                    : 'Unknown Type';
                                $claim->claim_type_code = $claim->claimType 
                                    ? $claim->claimType->code 
                                    : 'N/A';
                                return $claim;
                            });
                        \Log::info('Eloquent claims query successful: ' . $claims->count() . ' claims found');
                    } catch (\Exception $eloquentError) {
                        \Log::warning('Eloquent claims query failed, falling back to PDO: ' . $eloquentError->getMessage());
                        // Fallback to PDO query
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
                    }
                    \Log::info('Found ' . $claims->count() . ' claims');
                    
                    \Log::info('PDO SUCCESS - Retrieved ' . $claimTypes->count() . ' claim types, ' . $employees->count() . ' employees, ' . $claims->count() . ' claims');
                    
            } catch (\Exception $pdoError) {
                \Log::error('PDO fallback failed: ' . $pdoError->getMessage());
                $claimTypes = collect([]);
                $employees = collect([]);
                $claims = collect([]);
            }
            
            // Calculate statistics
            $totalClaimTypes = $claimTypes->count();
            $totalClaims = $claims->count();
            $pendingClaims = $claims->where('status', 'pending')->count();
            $approvedClaims = $claims->where('status', 'approved')->count();
            $totalAmount = $claims->where('status', 'approved')->sum('amount');
            
            \Log::info('Passing to view - Claims count: ' . $claims->count());
            if ($claims->count() > 0) {
                \Log::info('First claim data: ' . json_encode($claims->first()));
            }
            
            return view('claims.reimbursement', compact(
                'claimTypes', 
                'employees', 
                'claims',
                'totalClaimTypes',
                'totalClaims',
                'pendingClaims', 
                'approvedClaims',
                'totalAmount'
            ));
        } catch (\Exception $e) {
            \Log::error('Claims index error: ' . $e->getMessage());
            return view('claims.reimbursement', [
                'claimTypes' => collect([]),
                'employees' => collect([]),
                'claims' => collect([]),
                'totalClaimTypes' => 0,
                'totalClaims' => 0,
                'pendingClaims' => 0,
                'approvedClaims' => 0,
                'totalAmount' => 0
            ]);
        }
    }

    public function getClaims(Request $request)
    {
        $query = Claim::with(['employee', 'claimType', 'approvedBy']);
        
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->claim_type_id) {
            $query->where('claim_type_id', $request->claim_type_id);
        }

        if ($request->date_from) {
            $query->where('submitted_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('submitted_date', '<=', $request->date_to);
        }

        $claims = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $claims
        ]);
    }

    public function create()
    {
        return view('claims.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'claim_type_id' => 'required|exists:claim_types,id',
            'amount' => 'required|numeric|min:0',
            'claim_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        // Get a valid employee ID
        $employeeId = Auth::user()->employee_id ?? null;
        
        // If no authenticated user employee ID, get the first available employee
        if (!$employeeId) {
            try {
                $firstEmployee = Employee::where('status', 'active')->orderBy('id')->first();
                $employeeId = $firstEmployee ? $firstEmployee->id : null;
            } catch (\Exception $e) {
                $firstEmployee = DB::selectOne("SELECT id FROM employees WHERE status = 'active' ORDER BY id LIMIT 1");
                $employeeId = $firstEmployee ? $firstEmployee->id : null;
            }
        }
        
        // Verify the employee exists
        if ($employeeId) {
            try {
                $employeeExists = Employee::find($employeeId);
                if (!$employeeExists) {
                    $firstEmployee = Employee::where('status', 'active')->orderBy('id')->first();
                    $employeeId = $firstEmployee ? $firstEmployee->id : null;
                }
            } catch (\Exception $e) {
                $employeeExists = DB::selectOne("SELECT id FROM employees WHERE id = ?", [$employeeId]);
                if (!$employeeExists) {
                    $firstEmployee = DB::selectOne("SELECT id FROM employees WHERE status = 'active' ORDER BY id LIMIT 1");
                    $employeeId = $firstEmployee ? $firstEmployee->id : null;
                }
            }
        }
        
        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'No valid employee found. Please ensure at least one employee exists in the system.'
            ], 400);
        }

        // Check claim type limits
        $claimType = ClaimType::find($request->claim_type_id);
        if ($claimType->max_amount > 0 && $request->amount > $claimType->max_amount) {
            return response()->json([
                'success' => false,
                'message' => "Amount exceeds maximum limit of $" . number_format($claimType->max_amount, 2)
            ]);
        }

        // Handle file upload (optional)
        $receiptPath = null;
        if ($request->hasFile('attachment')) {
            $receiptPath = $request->file('attachment')->store('receipts', 'public');
        }
        // Note: Attachments are now optional for all claim types

        // Auto-approve if conditions are met
        $status = 'pending';
        $approvedBy = null;
        $approvedAt = null;
        
        if ($claimType && $claimType->auto_approve && 
            ($claimType->max_amount == 0 || $request->amount <= $claimType->max_amount)) {
            $status = 'approved';
            // For auto-approval, don't set approved_by to avoid foreign key issues
            $approvedBy = null; // System auto-approval
            $approvedAt = now();
        }

        $claim = Claim::create([
            'employee_id' => $employeeId,
            'claim_type_id' => $request->claim_type_id,
            'amount' => $request->amount,
            'claim_date' => $request->claim_date,
            'description' => $request->description,
            'receipt_path' => $receiptPath,
            'status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === 'approved' ? 'Claim auto-approved and submitted successfully' : 'Claim submitted successfully',
            'data' => $claim->load(['employee', 'claimType'])
        ]);
    }

    public function show($id)
    {
        try {
            $claim = Claim::with(['employee', 'claimType', 'approvedBy'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $claim->id,
                    'employee_name' => $claim->employee->first_name . ' ' . $claim->employee->last_name,
                    'claim_type_name' => $claim->claimType->name,
                    'amount' => $claim->amount,
                    'claim_date' => $claim->claim_date,
                    'description' => $claim->description,
                    'status' => $claim->status,
                    'has_attachment' => !empty($claim->receipt_path)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading claim details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        // In a real application, fetch from database
        $claim = [
            'id' => $id,
            'employee_name' => 'John Doe',
            'type' => 'travel',
            'amount' => 250.00,
            'date' => '2024-01-15',
            'description' => 'Business trip to client site',
            'status' => 'pending',
            'receipt' => null
        ];

        return view('claims.edit', compact('claim'));
    }

    public function update(Request $request, $id)
    {
        try {
            $claim = Claim::findOrFail($id);
            
            if ($claim->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update approved/rejected/paid claim'
                ], 400);
            }

            $request->validate([
                'claim_type_id' => 'required|exists:claim_types,id',
                'amount' => 'required|numeric|min:0',
                'claim_date' => 'required|date',
                'description' => 'required|string|max:1000',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);

            $claimType = ClaimType::find($request->claim_type_id);
            if ($claimType->max_amount > 0 && $request->amount > $claimType->max_amount) {
                return response()->json([
                    'success' => false,
                    'message' => "Amount exceeds maximum limit of {$claimType->max_amount}"
                ], 400);
            }

            $receiptPath = $claim->receipt_path;
            if ($request->hasFile('attachment')) {
                if ($receiptPath) {
                    Storage::disk('public')->delete($receiptPath);
                }
                $receiptPath = $request->file('attachment')->store('receipts', 'public');
            }

            $claim->update([
                'claim_type_id' => $request->claim_type_id,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'description' => $request->description,
                'receipt_path' => $receiptPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim updated successfully',
                'data' => $claim->load(['employee', 'claimType'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating claim: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            // Allow deletion of any claim (remove status restriction for now)
            // if ($claim->status !== 'pending') {
            //     return redirect()->route('claims-reimbursement')->with('error', 'Cannot delete approved/rejected/paid claim');
            // }

            // Delete receipt file if exists
            if ($claim->receipt_path) {
                try {
                    Storage::disk('public')->delete($claim->receipt_path);
                } catch (\Exception $fileError) {
                    \Log::warning('Could not delete file: ' . $fileError->getMessage());
                }
            }

            $affected = DB::delete("DELETE FROM claims WHERE id = ?", [$id]);

            if ($affected > 0) {
                return redirect()->route('claims-reimbursement')->with('success', 'Claim deleted successfully!');
            } else {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found or already deleted');
            }
        } catch (\Exception $e) {
            \Log::error('Delete claim error: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')->with('error', 'Error deleting claim: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            // Use direct DB query for web form submission
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->back()->with('error', 'Claim not found');
            }
            
            if ($claim->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending claims can be approved');
            }

            DB::update("UPDATE claims SET status = 'approved', approved_by = ?, approved_at = ?, updated_at = ? WHERE id = ?", [
                Auth::id() ?? 1,
                now(),
                now(),
                $id
            ]);

            return redirect()->back()->with('success', 'Claim approved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving claim: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            // Use direct DB query for web form submission
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->back()->with('error', 'Claim not found');
            }
            
            if ($claim->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending claims can be rejected');
            }

            DB::update("UPDATE claims SET status = 'rejected', approved_by = ?, approved_at = ?, rejection_reason = ?, updated_at = ? WHERE id = ?", [
                Auth::id() ?? 1,
                now(),
                'Rejected by administrator',
                now(),
                $id
            ]);

            return redirect()->back()->with('success', 'Claim rejected successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error rejecting claim: ' . $e->getMessage());
        }
    }

    public function markPaid(Request $request, $id)
    {
        try {
            // Use direct DB query for web form submission
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->back()->with('error', 'Claim not found');
            }
            
            if ($claim->status !== 'approved') {
                return redirect()->back()->with('error', 'Only approved claims can be marked as paid');
            }

            DB::update("UPDATE claims SET status = 'paid', paid_at = ?, updated_at = ? WHERE id = ?", [
                now(),
                now(),
                $id
            ]);

            return redirect()->back()->with('success', 'Claim marked as paid successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error marking claim as paid: ' . $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'claim_ids' => 'required|array',
            'claim_ids.*' => 'exists:claims,id'
        ]);

        $approved = 0;
        foreach ($request->claim_ids as $id) {
            $claim = Claim::find($id);
            if ($claim && $claim->status === 'pending') {
                $claim->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
                $approved++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$approved claims approved successfully"
        ]);
    }

    // Claim Types Management
    public function getClaimTypes()
    {
        $claimTypes = ClaimType::where('is_active', true)->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $claimTypes
        ]);
    }

    public function storeClaimType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:claim_types,name',
            'code' => 'required|string|max:10|unique:claim_types,code',
            'description' => 'nullable|string|max:500',
            'max_amount' => 'nullable|numeric|min:0',
            'requires_attachment' => 'boolean',
            'auto_approve' => 'boolean'
        ]);

        $claimType = ClaimType::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'max_amount' => $request->max_amount,
            'requires_attachment' => $request->boolean('requires_attachment', false),
            'auto_approve' => $request->boolean('auto_approve', false),
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Claim type created successfully',
            'data' => $claimType
        ]);
    }

    public function updateClaimType(Request $request, $id)
    {
        $claimType = ClaimType::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:100|unique:claim_types,name,' . $id,
            'code' => 'required|string|max:10|unique:claim_types,code,' . $id,
            'description' => 'nullable|string|max:500',
            'max_amount' => 'nullable|numeric|min:0',
            'requires_attachment' => 'boolean',
            'auto_approve' => 'boolean'
        ]);

        $claimType->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'max_amount' => $request->max_amount,
            'requires_attachment' => $request->boolean('requires_attachment', false),
            'auto_approve' => $request->boolean('auto_approve', false)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Claim type updated successfully',
            'data' => $claimType
        ]);
    }

    public function showClaimType($id)
    {
        try {
            $claimType = ClaimType::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $claimType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading claim type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyClaimType($id)
    {
        try {
            $claimType = ClaimType::findOrFail($id);
            
            // Check if claim type is being used
            $claimsCount = Claim::where('claim_type_id', $id)->count();
            if ($claimsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete claim type that is being used by existing claims'
                ], 409);
            }

            $claimType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Claim type deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting claim type: ' . $e->getMessage()
            ], 500);
        }
    }

    // Claims Statistics
    public function getClaimsStats()
    {
        try {
            $totalClaims = Claim::count();
            $pendingClaims = Claim::where('status', 'pending')->count();
            $approvedClaims = Claim::where('status', 'approved')->count();
            $rejectedClaims = Claim::where('status', 'rejected')->count();
            $paidClaims = Claim::where('status', 'paid')->count();
            $totalAmount = Claim::whereIn('status', ['approved', 'paid'])->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_claims' => $totalClaims,
                    'pending_claims' => $pendingClaims,
                    'approved_claims' => $approvedClaims,
                    'rejected_claims' => $rejectedClaims,
                    'paid_claims' => $paidClaims,
                    'total_amount' => $totalAmount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching claims statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    // Validate attachments
    public function validateAttachments()
    {
        try {
            $pendingClaims = Claim::where('status', 'pending')
                ->with(['claimType'])
                ->get();

            $validated = 0;
            $errors = [];

            foreach ($pendingClaims as $claim) {
                if ($claim->claimType->requires_attachment && !$claim->receipt_path) {
                    $errors[] = "Claim #{$claim->id} requires attachment but none provided";
                } else {
                    $validated++;
                }
            }

            return response()->json([
                'success' => count($errors) === 0,
                'message' => count($errors) === 0 
                    ? "All {$validated} pending claims have valid attachments" 
                    : "Validation issues found",
                'data' => [
                    'validated_count' => $validated,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating attachments: ' . $e->getMessage()
            ], 500);
        }
    }

    // Forward to payroll (mark as paid)
    public function forwardToPayroll(Request $request)
    {
        try {
            $approvedClaims = Claim::where('status', 'approved')->get();
            $forwarded = 0;

            foreach ($approvedClaims as $claim) {
                $claim->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
                $forwarded++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$forwarded} approved claims forwarded to payroll",
                'data' => ['forwarded_count' => $forwarded]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error forwarding to payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    // Web-based methods for server-side handling
    public function storeWeb(Request $request)
    {
        \Log::info('=== STOREWEB METHOD CALLED ===');
        \Log::info('SUCCESS! The storeWeb method is being called correctly!');
        \Log::info('All request data: ' . json_encode($request->all()));
        \Log::info('Employee ID from request: ' . $request->input('employee_id', 'NOT SET'));
        \Log::info('Claim Type ID from request: ' . $request->input('claim_type_id', 'NOT SET'));
        \Log::info('Amount from request: ' . $request->input('amount', 'NOT SET'));
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request expects JSON: ' . ($request->expectsJson() ? 'YES' : 'NO'));
        
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|numeric',
            'claim_type_id' => 'required|numeric',
            'amount' => 'required|numeric|min:0',
            'claim_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            \Log::error('Claim validation failed: ' . json_encode($validator->errors()));
            
            return redirect()->route('claims-reimbursement')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Ensure we're using the correct database
            \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', 'hr3systemdb');
            DB::purge('mysql');
            
            // SIMPLIFIED: Use the employee ID from the form, fallback to Jane Smith if needed
            $employeeId = $request->employee_id;
            \Log::info('Employee ID received: ' . $employeeId);
            
            // If no employee ID provided, use Jane Smith (ID: 2) as default
            if (!$employeeId || $employeeId == '' || $employeeId == '0') {
                $employeeId = 2; // Jane Smith
                \Log::info('No valid employee ID, using Jane Smith (ID: 2) as default');
            }
            
            \Log::info('Final employee ID to use: ' . $employeeId);

            // Check claim type limits
            $claimType = DB::selectOne("SELECT * FROM claim_types WHERE id = ?", [$request->claim_type_id]);
            if ($claimType && isset($claimType->max_amount) && $claimType->max_amount > 0 && $request->amount > $claimType->max_amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Amount exceeds maximum limit of $" . number_format($claimType->max_amount, 2));
            }

            // Handle file upload (optional)
            $receiptPath = null;
            if ($request->hasFile('attachment')) {
                $receiptPath = $request->file('attachment')->store('receipts', 'public');
            }
            // Note: Attachments are now optional for all claim types

            // Auto-approve if conditions are met
            $status = 'pending';
            $approvedBy = null;
            $approvedAt = null;
            
            if ($claimType && isset($claimType->auto_approve) && $claimType->auto_approve && 
                ($claimType->max_amount == 0 || $request->amount <= $claimType->max_amount)) {
                $status = 'approved';
                // For auto-approval, leave approved_by as null to indicate system approval
                $approvedBy = null;
                $approvedAt = now();
            }

            // Create claim using Eloquent model
            $claim = Claim::create([
                'employee_id' => $employeeId,
                'claim_type_id' => $request->claim_type_id,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'description' => $request->description,
                'receipt_path' => $receiptPath,
                'attachment_path' => $receiptPath, // Add this for compatibility
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt
            ]);
            
            \Log::info('Claim created successfully with ID: ' . $claim->id);

            $message = $status === 'approved' ? 'Claim auto-approved and submitted successfully!' : 'Claim submitted successfully!';
            
            // Always return redirect for web form submissions
            return redirect()->route('claims-reimbursement')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create claim: ' . $e->getMessage());
        }
    }

    private function createSampleEmployees($pdo)
    {
        try {
            // Check if employees table exists, if not create it
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert sample employees
            $employees = [
                ['John', 'Doe', 'john.doe@jetlouge.com', '555-0101', 'Software Developer', 'IT', '2023-01-15', 75000],
                ['Jane', 'Smith', 'jane.smith@jetlouge.com', '555-0102', 'Project Manager', 'IT', '2022-03-20', 85000],
                ['Mike', 'Johnson', 'mike.johnson@jetlouge.com', '555-0103', 'HR Specialist', 'HR', '2023-06-10', 65000],
                ['Sarah', 'Wilson', 'sarah.wilson@jetlouge.com', '555-0104', 'Accountant', 'Finance', '2022-11-05', 70000],
                ['Tom', 'Brown', 'tom.brown@jetlouge.com', '555-0105', 'Sales Representative', 'Sales', '2023-02-28', 60000]
            ];
            
            foreach ($employees as $emp) {
                $pdo->prepare("INSERT INTO employees (first_name, last_name, email, phone, position, department, hire_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')")
                    ->execute($emp);
            }
            
            \Log::info('Created ' . count($employees) . ' sample employees');
        } catch (\Exception $e) {
            \Log::error('Error creating sample employees: ' . $e->getMessage());
        }
    }

    public function editWeb($id)
    {
        try {
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            if ($claim->status !== 'pending') {
                return redirect()->route('claims-reimbursement')->with('error', 'Cannot edit approved/rejected/paid claim');
            }

            // Store claim data in session for editing and flag to show modal
            session([
                'edit_claim' => (array)$claim,
                'show_edit_modal' => true
            ]);
            return redirect()->route('claims-reimbursement')->with('info', 'Claim loaded for editing');
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error loading claim for editing');
        }
    }

    public function updateWeb(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'claim_type_id' => 'required|exists:claim_types,id',
            'amount' => 'required|numeric|min:0',
            'claim_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            if ($claim->status !== 'pending') {
                return redirect()->route('claims-reimbursement')->with('error', 'Cannot update approved/rejected/paid claim');
            }

            // Check claim type limits
            $claimType = DB::selectOne("SELECT * FROM claim_types WHERE id = ?", [$request->claim_type_id]);
            if ($claimType && $claimType->max_amount > 0 && $request->amount > $claimType->max_amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Amount exceeds maximum limit of $" . number_format($claimType->max_amount, 2));
            }

            $receiptPath = $claim->receipt_path;
            if ($request->hasFile('attachment')) {
                if ($receiptPath) {
                    Storage::disk('public')->delete($receiptPath);
                }
                $receiptPath = $request->file('attachment')->store('receipts', 'public');
            }

            $affected = DB::update(
                "UPDATE claims SET claim_type_id = ?, amount = ?, claim_date = ?, description = ?, receipt_path = ?, updated_at = ? WHERE id = ?",
                [$request->claim_type_id, $request->amount, $request->claim_date, $request->description, $receiptPath, now(), $id]
            );

            if ($affected === 0) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            return redirect()->route('claims-reimbursement')->with('success', 'Claim updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating claim: ' . $e->getMessage());
        }
    }

    public function viewClaim($id)
    {
        try {
            $claim = DB::selectOne("
                SELECT c.*, e.first_name, e.last_name, ct.name as claim_type_name 
                FROM claims c
                JOIN employees e ON c.employee_id = e.id
                JOIN claim_types ct ON c.claim_type_id = ct.id
                WHERE c.id = ?
            ", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            $claimDate = date('M d, Y', strtotime($claim->claim_date));
            $submittedDate = date('M d, Y', strtotime($claim->created_at));
            
            return redirect()->route('claims-reimbursement')->with('success', 
                "Claim Details:\n\n" .
                "ID: {$claim->id}\n" .
                "Employee: {$claim->first_name} {$claim->last_name}\n" .
                "Type: {$claim->claim_type_name}\n" .
                "Amount: $" . number_format($claim->amount, 2) . "\n" .
                "Claim Date: {$claimDate}\n" .
                "Submitted: {$submittedDate}\n" .
                "Status: " . ucfirst($claim->status) . "\n" .
                "Description: {$claim->description}\n" .
                "Has Attachment: " . ($claim->receipt_path ? 'Yes' : 'No')
            );
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error loading claim details');
        }
    }

    public function destroyWeb($id)
    {
        try {
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            if ($claim->status !== 'pending') {
                return redirect()->route('claims-reimbursement')->with('error', 'Cannot delete approved/rejected/paid claim');
            }

            // Delete receipt file if exists
            if ($claim->receipt_path) {
                Storage::disk('public')->delete($claim->receipt_path);
            }

            $affected = DB::delete("DELETE FROM claims WHERE id = ?", [$id]);

            if ($affected === 0) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            return redirect()->route('claims-reimbursement')->with('success', 'Claim deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error deleting claim: ' . $e->getMessage());
        }
    }

    public function approveWeb($id)
    {
        try {
            // Ensure we're using the correct database
            \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', 'hr3systemdb');
            DB::purge('mysql');
            
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            if ($claim->status !== 'pending') {
                return redirect()->route('claims-reimbursement')->with('error', 'Only pending claims can be approved');
            }

            // Get a valid employee ID for approval
            $approverId = Auth::id() ?? null;
            
            // If no authenticated user, get the first available employee
            if (!$approverId) {
                $firstEmployee = DB::selectOne("SELECT id FROM employees WHERE status = 'active' ORDER BY id LIMIT 1");
                $approverId = $firstEmployee ? $firstEmployee->id : null;
            }
            
            // Verify the approver exists in employees table
            if ($approverId) {
                $approverExists = DB::selectOne("SELECT id FROM employees WHERE id = ?", [$approverId]);
                if (!$approverExists) {
                    // Use the first available employee as fallback
                    $firstEmployee = DB::selectOne("SELECT id FROM employees WHERE status = 'active' ORDER BY id LIMIT 1");
                    $approverId = $firstEmployee ? $firstEmployee->id : null;
                }
            }
            
            if (!$approverId) {
                return redirect()->route('claims-reimbursement')->with('error', 'No valid employee found for approval. Please ensure at least one employee exists.');
            }

            $affected = DB::update(
                "UPDATE claims SET status = ?, updated_at = ? WHERE id = ?",
                ['approved', now(), $id]
            );

            return redirect()->route('claims-reimbursement')->with('success', 'Claim approved successfully!');
        } catch (\Exception $e) {
            \Log::error('Claim approval failed: ' . $e->getMessage());
            return redirect()->route('claims-reimbursement')->with('error', 'Error approving claim: Please ensure all required data is valid.');
        }
    }

    public function rejectWeb($id)
    {
        try {
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            if ($claim->status !== 'pending') {
                return redirect()->route('claims-reimbursement')->with('error', 'Only pending claims can be rejected');
            }

            $affected = DB::update(
                "UPDATE claims SET status = ?, updated_at = ? WHERE id = ?",
                ['rejected', now(), $id]
            );

            return redirect()->route('claims-reimbursement')->with('success', 'Claim rejected successfully!');
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error rejecting claim: ' . $e->getMessage());
        }
    }

    public function payWeb($id)
    {
        try {
            $claim = DB::selectOne("SELECT * FROM claims WHERE id = ?", [$id]);
            
            if (!$claim) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim not found');
            }

            if ($claim->status !== 'approved') {
                return redirect()->route('claims-reimbursement')->with('error', 'Only approved claims can be marked as paid');
            }

            $affected = DB::update(
                "UPDATE claims SET status = ?, updated_at = ? WHERE id = ?",
                ['paid', now(), $id]
            );

            return redirect()->route('claims-reimbursement')->with('success', 'Claim marked as paid successfully!');
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error marking claim as paid: ' . $e->getMessage());
        }
    }

    // Claim Types Web Methods
    public function storeClaimTypeWeb(Request $request)
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            // Check if code column exists, if not, create it
            try {
                DB::statement("ALTER TABLE claim_types ADD COLUMN IF NOT EXISTS code VARCHAR(10) DEFAULT 'N/A'");
                DB::statement("ALTER TABLE claim_types ADD COLUMN IF NOT EXISTS requires_attachment BOOLEAN DEFAULT FALSE");
                DB::statement("ALTER TABLE claim_types ADD COLUMN IF NOT EXISTS auto_approve BOOLEAN DEFAULT FALSE");
                DB::statement("ALTER TABLE claim_types ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE");
            } catch (\Exception $e) {
                // Columns might already exist, continue
            }

            DB::table('claim_types')->insert([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'max_amount' => $request->max_amount ?? 0,
                'requires_attachment' => $request->boolean('requires_attachment', false),
                'auto_approve' => $request->boolean('auto_approve', false),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Clear any edit session data
            session()->forget('edit_claim_type');
            return redirect()->route('claims-reimbursement')->with('success', 'Claim type created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create claim type: ' . $e->getMessage());
        }
    }

    public function editClaimTypeWeb($id)
    {
        try {
            $claimType = DB::selectOne("SELECT * FROM claim_types WHERE id = ?", [$id]);
            
            if (!$claimType) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim type not found');
            }

            // Store claim type data in session for editing
            session(['edit_claim_type' => (array)$claimType]);
            return redirect()->route('claims-reimbursement')->with('info', 'Claim type loaded for editing');
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error loading claim type for editing');
        }
    }

    public function updateClaimTypeWeb(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:claim_types,name,' . $id,
            'code' => 'required|string|max:10|unique:claim_types,code,' . $id,
            'description' => 'nullable|string|max:500',
            'max_amount' => 'nullable|numeric|min:0',
            'requires_attachment' => 'boolean',
            'auto_approve' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors and try again.');
        }

        try {
            $claimType = DB::selectOne("SELECT * FROM claim_types WHERE id = ?", [$id]);
            
            if (!$claimType) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim type not found');
            }

            $affected = DB::update(
                "UPDATE claim_types SET name = ?, code = ?, description = ?, max_amount = ?, requires_attachment = ?, auto_approve = ?, updated_at = ? WHERE id = ?",
                [$request->name, strtoupper($request->code), $request->description, $request->max_amount, $request->boolean('requires_attachment', false), $request->boolean('auto_approve', false), now(), $id]
            );

            // Clear edit session data after successful update
            session()->forget('edit_claim_type');
            return redirect()->route('claims-reimbursement')->with('success', 'Claim type updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating claim type: ' . $e->getMessage());
        }
    }

    public function viewClaimTypeWeb($id)
    {
        try {
            $claimType = DB::selectOne("SELECT * FROM claim_types WHERE id = ?", [$id]);
            
            if (!$claimType) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim type not found');
            }

            return redirect()->route('claims-reimbursement')->with('success', 
                "Claim Type Details:\n\n" .
                "ID: {$claimType->id}\n" .
                "Name: {$claimType->name}\n" .
                "Code: {$claimType->code}\n" .
                "Max Amount: " . ($claimType->max_amount ? '$' . number_format($claimType->max_amount, 2) : 'No limit') . "\n" .
                "Requires Attachment: " . ($claimType->requires_attachment ? 'Yes' : 'No') . "\n" .
                "Auto Approve: " . ($claimType->auto_approve ? 'Yes' : 'No') . "\n" .
                "Status: " . ($claimType->is_active ? 'Active' : 'Inactive') . "\n" .
                "Description: " . ($claimType->description ?: 'No description')
            );
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error loading claim type details');
        }
    }

    public function destroyClaimTypeWeb($id)
    {
        try {
            $claimType = DB::selectOne("SELECT * FROM claim_types WHERE id = ?", [$id]);
            
            if (!$claimType) {
                return redirect()->route('claims-reimbursement')->with('error', 'Claim type not found');
            }

            // Check if claim type is being used
            $claimsCount = DB::selectOne("SELECT COUNT(*) as count FROM claims WHERE claim_type_id = ?", [$id]);
            if ($claimsCount && $claimsCount->count > 0) {
                return redirect()->route('claims-reimbursement')->with('error', 'Cannot delete claim type that is being used by existing claims');
            }

            $affected = DB::delete("DELETE FROM claim_types WHERE id = ?", [$id]);

            return redirect()->route('claims-reimbursement')->with('success', 'Claim type deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('claims-reimbursement')->with('error', 'Error deleting claim type: ' . $e->getMessage());
        }
    }
    
    private function createClaimTables($pdo)
    {
        // Create claim_types table only if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS claim_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                code VARCHAR(10) NOT NULL UNIQUE,
                description TEXT,
                max_amount DECIMAL(10,2) DEFAULT NULL,
                requires_attachment BOOLEAN DEFAULT FALSE,
                auto_approve BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
        
        // Create claims table only if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS claims (
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
                INDEX idx_employee_id (employee_id),
                INDEX idx_claim_type_id (claim_type_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB AUTO_INCREMENT=1
        ");
        
        // Add foreign keys only if they don't exist
        try {
            // Check if foreign keys exist before adding them
            $result = $pdo->query("
                SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = 'hr3systemdb' 
                AND TABLE_NAME = 'claims' 
                AND CONSTRAINT_NAME = 'fk_claims_employee'
            ")->fetchColumn();
            
            if ($result == 0) {
                $pdo->exec("ALTER TABLE claims ADD CONSTRAINT fk_claims_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE");
                $pdo->exec("ALTER TABLE claims ADD CONSTRAINT fk_claims_claim_type FOREIGN KEY (claim_type_id) REFERENCES claim_types(id) ON DELETE CASCADE");
                $pdo->exec("ALTER TABLE claims ADD CONSTRAINT fk_claims_approved_by FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL");
            }
        } catch (Exception $e) {
            // Foreign keys might fail if employees table doesn't exist
            \Log::warning('Could not add foreign keys to claims table: ' . $e->getMessage());
        }
        
        // Insert sample claim types if table is empty
        $count = $pdo->query("SELECT COUNT(*) FROM claim_types")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("
                INSERT INTO claim_types (name, code, description, max_amount, requires_attachment) VALUES
                ('Travel Expenses', 'TRAVEL', 'Business travel related expenses', 5000.00, true),
                ('Office Supplies', 'OFFICE', 'Office supplies and equipment', 1000.00, true),
                ('Meal Allowance', 'MEAL', 'Business meal expenses', 500.00, true),
                ('Training Costs', 'TRAINING', 'Professional development and training', 2000.00, true),
                ('Medical Expenses', 'MEDICAL', 'Medical and health related expenses', 3000.00, true)
            ");
        }
        
        // Insert sample claims if table is empty
        $claimsCount = $pdo->query("SELECT COUNT(*) FROM claims")->fetchColumn();
        if ($claimsCount == 0) {
            // Get first employee and claim type for sample data
            $employee = $pdo->query("SELECT id FROM employees ORDER BY id LIMIT 1")->fetch(PDO::FETCH_OBJ);
            $claimType = $pdo->query("SELECT id FROM claim_types ORDER BY id LIMIT 1")->fetch(PDO::FETCH_OBJ);
            
            if ($employee && $claimType) {
                $pdo->exec("
                    INSERT INTO claims (employee_id, claim_type_id, amount, claim_date, description, status) VALUES
                    ({$employee->id}, {$claimType->id}, 250.00, CURDATE(), 'Sample travel expense claim', 'pending'),
                    ({$employee->id}, {$claimType->id}, 150.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Office supplies purchase', 'approved'),
                    ({$employee->id}, {$claimType->id}, 75.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Business lunch meeting', 'paid')
                ");
            }
        }
    }
}
