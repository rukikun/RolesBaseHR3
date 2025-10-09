<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Claim;
use App\Models\Employee;
use App\Models\ValidatedAttachment;

class ValidateAttachmentController extends Controller
{
    /**
     * Display the validate attachment page with approved claims
     */
    public function index()
    {
        try {
            // Get approved claims with attachments
            $approvedClaims = collect([]);
            
            try {
                // Try using Eloquent first
                $approvedClaims = Claim::with(['employee', 'claimType', 'approver'])
                    ->where('status', 'approved')
                    ->orderBy('approved_at', 'desc')
                    ->get()
                    ->map(function($claim) {
                        // Add computed properties for blade compatibility
                        $claim->employee_name = ($claim->employee->first_name ?? 'Employee') . ' ' . ($claim->employee->last_name ?? 'Unknown');
                        $claim->claim_type_name = $claim->claimType->name ?? 'Unknown Type';
                        $claim->claim_type_code = $claim->claimType->code ?? 'N/A';
                        return $claim;
                    });
                
                Log::info('Eloquent - Retrieved ' . $approvedClaims->count() . ' approved claims with attachments');
            } catch (\Exception $e) {
                Log::warning('Eloquent failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw PDO queries
                try {
                    $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Get approved claims with attachments
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
                        WHERE c.status = 'approved'
                        ORDER BY c.approved_at DESC
                    ");
                    $claimsData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $approvedClaims = collect($claimsData);
                    
                    Log::info('Raw PDO - Retrieved ' . count($claimsData) . ' approved claims with attachments');
                } catch (\Exception $e2) {
                    Log::error('Raw PDO also failed: ' . $e2->getMessage());
                    $approvedClaims = collect([]);
                }
            }
            
            // Calculate statistics
            $totalApprovedClaims = $approvedClaims->count();
            $totalApprovedAmount = $approvedClaims->sum('amount');
            $claimsWithAttachments = $approvedClaims->whereNotNull('receipt_path')->count();
            $pendingValidation = $approvedClaims->where('status', 'approved')->count();
                
            return view('claims.validate_attachment', compact(
                'approvedClaims', 
                'totalApprovedClaims', 
                'totalApprovedAmount', 
                'claimsWithAttachments',
                'pendingValidation'
            ));
            
        } catch (\Exception $e) {
            Log::error('Validate attachment index error: ' . $e->getMessage());
            
            return view('claims.validate_attachment', [
                'approvedClaims' => collect([]),
                'totalApprovedClaims' => 0,
                'totalApprovedAmount' => 0,
                'claimsWithAttachments' => 0,
                'pendingValidation' => 0
            ])->with('error', 'Error loading approved claims data: ' . $e->getMessage());
        }
    }

    /**
     * Validate attachment for a specific claim and send to payroll
     */
    public function validateAttachment($id)
    {
        try {
            // Try Eloquent first
            try {
                $claim = Claim::findOrFail($id);
                
                if ($claim->status !== 'approved') {
                    return redirect()->route('validate-attachment')
                        ->with('error', 'Only approved claims can have attachments validated.');
                }

                // Get additional claim data with joins
                $claimData = DB::table('claims as c')
                    ->leftJoin('employees as e', 'c.employee_id', '=', 'e.id')
                    ->leftJoin('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
                    ->select(
                        'c.*',
                        DB::raw("CONCAT(COALESCE(e.first_name, 'Employee'), ' ', COALESCE(e.last_name, CONCAT('ID:', c.employee_id))) as full_employee_name"),
                        'ct.name as claim_type_name'
                    )
                    ->where('c.id', $claim->id)
                    ->first();

                // Create validated attachment record with proper data
                ValidatedAttachment::create([
                    'claim_id' => $claim->id,
                    'employee_id' => $claim->employee_id ?? 1,
                    'employee_name' => $claimData->full_employee_name ?? 'Employee ' . ($claim->employee_id ?? 1),
                    'claim_type' => $claimData->claim_type_name ?? 'General Claim',
                    'amount' => $claim->amount ?? 0,
                    'claim_date' => $claim->claim_date ?? now(),
                    'description' => $claim->description ?? 'No description provided',
                    'attachment_path' => $claim->receipt_path ?? $claim->attachment_path ?? '',
                    'status' => 'validated',
                    'validated_at' => now(),
                    'validated_by' => 1 // Default admin ID
                ]);

                // Mark claim as validated
                $claim->update([
                    'attachment_validated' => true,
                    'validated_at' => now(),
                    'validated_by' => 1
                ]);

                return redirect()->route('payroll-management')
                    ->with('success', 'Attachment validated successfully and sent to payroll management!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Get claim data with joins
                $stmt = $pdo->prepare("
                    SELECT c.*, 
                           CONCAT(COALESCE(e.first_name, 'Employee'), ' ', COALESCE(e.last_name, CONCAT('ID:', c.employee_id))) as full_employee_name,
                           COALESCE(ct.name, 'General Claim') as claim_type_name
                    FROM claims c
                    LEFT JOIN employees e ON c.employee_id = e.id
                    LEFT JOIN claim_types ct ON c.claim_type_id = ct.id
                    WHERE c.id = ? AND c.status = 'approved'
                ");
                $stmt->execute([$id]);
                $claim = $stmt->fetch(\PDO::FETCH_OBJ);
                
                if (!$claim) {
                    return redirect()->route('validate-attachment')
                        ->with('error', 'Claim not found or not approved.');
                }
                
                // Insert validated attachment with proper data
                $stmt = $pdo->prepare("
                    INSERT INTO validated_attachments (claim_id, employee_id, employee_name, claim_type, amount, claim_date, description, attachment_path, status, validated_at, validated_by, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'validated', NOW(), 1, NOW(), NOW())
                ");
                $stmt->execute([
                    $claim->id,
                    $claim->employee_id ?? 1,
                    $claim->full_employee_name ?? 'Employee ' . ($claim->employee_id ?? 1),
                    $claim->claim_type_name ?? 'General Claim',
                    $claim->amount ?? 0,
                    $claim->claim_date ?? date('Y-m-d'),
                    $claim->description ?? 'No description provided',
                    $claim->receipt_path ?? ''
                ]);
                
                // Update claim
                $stmt = $pdo->prepare("
                    UPDATE claims 
                    SET attachment_validated = 1, validated_at = NOW(), validated_by = 1, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Attachment validated successfully and sent to payroll management!');
            }
            
        } catch (\Exception $e) {
            Log::error('Attachment validation error: ' . $e->getMessage());
            return redirect()->route('validate-attachment')
                ->with('error', 'Error validating attachment: ' . $e->getMessage());
        }
    }

    /**
     * Mark claim as ready for payroll
     */
    public function markForPayroll($id)
    {
        try {
            // Try Eloquent first
            try {
                $claim = Claim::findOrFail($id);
                
                if ($claim->status !== 'approved') {
                    return redirect()->route('validate-attachment')
                        ->with('error', 'Only approved claims can be marked for payroll.');
                }

                $claim->update([
                    'status' => 'ready_for_payroll',
                    'updated_at' => now()
                ]);

                return redirect()->route('validate-attachment')
                    ->with('success', 'Claim marked for payroll successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE claims 
                    SET status = 'ready_for_payroll', updated_at = NOW() 
                    WHERE id = ? AND status = 'approved'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('validate-attachment')
                    ->with('success', 'Claim marked for payroll successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Mark for payroll error: ' . $e->getMessage());
            return redirect()->route('validate-attachment')
                ->with('error', 'Error marking claim for payroll: ' . $e->getMessage());
        }
    }

    /**
     * Delete a claim
     */
    public function destroy($id)
    {
        try {
            // Try Eloquent first
            try {
                $claim = Claim::findOrFail($id);
                $claim->delete();

                return redirect()->route('validate-attachment')
                    ->with('success', 'Claim deleted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("DELETE FROM claims WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('validate-attachment')
                    ->with('success', 'Claim deleted successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Claim delete error: ' . $e->getMessage());
            return redirect()->route('validate-attachment')
                ->with('error', 'Error deleting claim: ' . $e->getMessage());
        }
    }
}
