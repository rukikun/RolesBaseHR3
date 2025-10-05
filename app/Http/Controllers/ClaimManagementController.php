<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Employee;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClaimManagementController extends Controller
{
    use DatabaseConnectionTrait;

    /**
     * Display a listing of claims
     */
    public function index(Request $request)
    {
        try {
            $query = Claim::with(['employee', 'claimType', 'approvedBy']);

            // Apply filters
            if ($request->has('employee_id') && $request->employee_id != '') {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->has('claim_type_id') && $request->claim_type_id != '') {
                $query->where('claim_type_id', $request->claim_type_id);
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->start_date != '') {
                $query->where('claim_date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date != '') {
                $query->where('claim_date', '<=', $request->end_date);
            }

            // Default to current year if no date filter
            if (!$request->has('start_date') && !$request->has('end_date')) {
                $query->whereYear('claim_date', Carbon::now()->year);
            }

            $claims = $query->orderBy('created_at', 'desc')->paginate(20);

            // Get data for filters
            $employees = Employee::active()->orderBy('first_name')->get();
            $claimTypes = ClaimType::active()->orderBy('name')->get();

            return view('admin.claims.index', compact('claims', 'employees', 'claimTypes'));
            
        } catch (\Exception $e) {
            Log::error('Error fetching claims: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading claims: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new claim
     */
    public function create()
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        $claimTypes = ClaimType::active()->orderBy('name')->get();
        
        return view('admin.claims.create', compact('employees', 'claimTypes'));
    }

    /**
     * Store a newly created claim
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'claim_type_id' => 'required|exists:claim_types,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'claim_date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|max:1000',
            'business_purpose' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Get claim type for validation
            $claimType = ClaimType::find($request->claim_type_id);
            
            // Check maximum amount limit
            if ($claimType->max_amount && $request->amount > $claimType->max_amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Amount exceeds maximum limit of {$claimType->max_amount} for {$claimType->name}.");
            }

            // Handle file upload
            $receiptPath = null;
            $receiptMetadata = null;
            
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('receipts', 'public');
                $receiptMetadata = [
                    'original_name' => $request->file('receipt')->getClientOriginalName(),
                    'size' => $request->file('receipt')->getSize(),
                    'mime_type' => $request->file('receipt')->getMimeType(),
                    'uploaded_at' => now()->toISOString()
                ];
            }

            // Generate reference number
            $referenceNumber = 'CLM-' . date('Y') . '-' . str_pad(Claim::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);

            // Determine initial status based on auto-approval rules
            $status = 'pending';
            $approvedBy = null;
            $approvedAt = null;
            
            if ($claimType->auto_approve && $claimType->auto_approve_limit && $request->amount <= $claimType->auto_approve_limit) {
                $status = 'approved';
                $approvedBy = Auth::id();
                $approvedAt = now();
            }

            $claim = Claim::create([
                'employee_id' => $request->employee_id,
                'claim_type_id' => $request->claim_type_id,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'description' => $request->description,
                'business_purpose' => $request->business_purpose,
                'receipt_path' => $receiptPath,
                'receipt_metadata' => $receiptMetadata,
                'reference_number' => $referenceNumber,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'notes' => $request->notes
            ]);

            Log::info('Claim created successfully: ' . $claim->id);
            
            $message = $status === 'approved' ? 'Claim created and auto-approved successfully!' : 'Claim created successfully!';
            return redirect()->route('claims.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error creating claim: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating claim: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified claim
     */
    public function show(Claim $claim)
    {
        try {
            $claim->load(['employee', 'claimType', 'approvedBy']);
            return view('admin.claims.show', compact('claim'));
        } catch (\Exception $e) {
            Log::error('Error showing claim: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading claim details.');
        }
    }

    /**
     * Show the form for editing the specified claim
     */
    public function edit(Claim $claim)
    {
        if (!in_array($claim->status, ['pending', 'rejected'])) {
            return redirect()->route('claims.index')
                ->with('error', 'Only pending or rejected claims can be edited.');
        }

        $employees = Employee::active()->orderBy('first_name')->get();
        $claimTypes = ClaimType::active()->orderBy('name')->get();
        
        return view('admin.claims.edit', compact('claim', 'employees', 'claimTypes'));
    }

    /**
     * Update the specified claim
     */
    public function update(Request $request, Claim $claim)
    {
        if (!in_array($claim->status, ['pending', 'rejected'])) {
            return redirect()->route('claims.index')
                ->with('error', 'Only pending or rejected claims can be edited.');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'claim_type_id' => 'required|exists:claim_types,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'claim_date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|max:1000',
            'business_purpose' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            // Get claim type for validation
            $claimType = ClaimType::find($request->claim_type_id);
            
            // Check maximum amount limit
            if ($claimType->max_amount && $request->amount > $claimType->max_amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Amount exceeds maximum limit of {$claimType->max_amount} for {$claimType->name}.");
            }

            $updateData = [
                'employee_id' => $request->employee_id,
                'claim_type_id' => $request->claim_type_id,
                'amount' => $request->amount,
                'claim_date' => $request->claim_date,
                'description' => $request->description,
                'business_purpose' => $request->business_purpose,
                'notes' => $request->notes,
                'status' => 'pending' // Reset to pending when edited
            ];

            // Handle file upload
            if ($request->hasFile('receipt')) {
                // Delete old receipt if exists
                if ($claim->receipt_path) {
                    Storage::disk('public')->delete($claim->receipt_path);
                }

                $updateData['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
                $updateData['receipt_metadata'] = [
                    'original_name' => $request->file('receipt')->getClientOriginalName(),
                    'size' => $request->file('receipt')->getSize(),
                    'mime_type' => $request->file('receipt')->getMimeType(),
                    'uploaded_at' => now()->toISOString()
                ];
            }

            $claim->update($updateData);

            Log::info('Claim updated successfully: ' . $claim->id);
            return redirect()->route('claims.index')
                ->with('success', 'Claim updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating claim: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating claim: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified claim
     */
    public function destroy(Claim $claim)
    {
        try {
            // Delete receipt file if exists
            if ($claim->receipt_path) {
                Storage::disk('public')->delete($claim->receipt_path);
            }

            $claimInfo = "Claim {$claim->reference_number} for {$claim->employee->full_name}";
            $claim->delete();

            Log::info('Claim deleted successfully: ' . $claimInfo);
            return redirect()->route('claims.index')
                ->with('success', 'Claim deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting claim: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting claim: ' . $e->getMessage());
        }
    }

    /**
     * Approve a claim
     */
    public function approve(Request $request, Claim $claim)
    {
        $validator = Validator::make($request->all(), [
            'approved_amount' => 'nullable|numeric|min:0.01|max:' . $claim->amount,
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Validation failed.');
        }

        try {
            if ($claim->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'Only pending claims can be approved.');
            }

            $claim->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approved_amount' => $request->approved_amount ?? $claim->amount,
                'notes' => $request->notes
            ]);

            Log::info('Claim approved: ' . $claim->id);
            return redirect()->back()
                ->with('success', 'Claim approved successfully!');

        } catch (\Exception $e) {
            Log::error('Error approving claim: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error approving claim: ' . $e->getMessage());
        }
    }

    /**
     * Reject a claim
     */
    public function reject(Request $request, Claim $claim)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Rejection reason is required.');
        }

        try {
            if ($claim->status !== 'pending') {
                return redirect()->back()
                    ->with('error', 'Only pending claims can be rejected.');
            }

            $claim->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ]);

            Log::info('Claim rejected: ' . $claim->id);
            return redirect()->back()
                ->with('success', 'Claim rejected successfully!');

        } catch (\Exception $e) {
            Log::error('Error rejecting claim: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error rejecting claim: ' . $e->getMessage());
        }
    }

    /**
     * Mark claim as paid
     */
    public function markAsPaid(Request $request, Claim $claim)
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Payment details are required.');
        }

        try {
            if ($claim->status !== 'approved') {
                return redirect()->back()
                    ->with('error', 'Only approved claims can be marked as paid.');
            }

            $claim->update([
                'status' => 'paid',
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method
            ]);

            Log::info('Claim marked as paid: ' . $claim->id);
            return redirect()->back()
                ->with('success', 'Claim marked as paid successfully!');

        } catch (\Exception $e) {
            Log::error('Error marking claim as paid: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Get claim statistics
     */
    public function getStats()
    {
        try {
            $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            $thisYear = [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            
            return [
                'pending' => Claim::where('status', 'pending')->count(),
                'approved_this_month' => Claim::where('status', 'approved')
                    ->whereBetween('claim_date', $thisMonth)->count(),
                'total_amount_this_year' => Claim::where('status', 'approved')
                    ->whereBetween('claim_date', $thisYear)->sum('approved_amount'),
                'awaiting_payment' => Claim::where('status', 'approved')->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting claim stats: ' . $e->getMessage());
            return [
                'pending' => 0,
                'approved_this_month' => 0,
                'total_amount_this_year' => 0,
                'awaiting_payment' => 0
            ];
        }
    }
}
