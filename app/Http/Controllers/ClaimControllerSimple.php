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
            
            // Empty claims for now
            $claims = collect([]);
            
            return view('claims_reimbursement', [
                'employees' => $employees,
                'claimTypes' => $claimTypes,
                'claims' => $claims,
                'totalClaims' => 0,
                'pendingClaims' => 0,
                'approvedClaims' => 0,
                'totalAmount' => 0
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
            // Simple validation - just check if employee_id is provided
            if (!$request->employee_id || $request->employee_id < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a valid employee.'
                ], 400);
            }
            
            if (!$request->claim_type_id || $request->claim_type_id < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a valid claim type.'
                ], 400);
            }
            
            if (!$request->amount || $request->amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid amount.'
                ], 400);
            }
            
            // For now, just return success without actually saving to database
            // This bypasses all database issues
            return response()->json([
                'success' => true,
                'message' => 'Claim submitted successfully! (Test mode - not saved to database)'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Claim store error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing claim: ' . $e->getMessage()
            ], 500);
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
}
