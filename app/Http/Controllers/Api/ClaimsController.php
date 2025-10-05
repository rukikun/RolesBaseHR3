<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Employee;
use App\Models\ClaimType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClaimsController extends Controller
{
    /**
     * Display a listing of claims.
     */
    public function index(Request $request)
    {
        try {
            $query = Claim::with(['employee', 'claimType', 'approver']);

            // Filter by employee if specified
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter by status if specified
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by claim type if specified
            if ($request->has('claim_type_id')) {
                $query->where('claim_type_id', $request->claim_type_id);
            }

            // Filter by date range if specified
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('claim_date', [$request->start_date, $request->end_date]);
            }

            $claims = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'status' => 'success',
                'data' => $claims,
                'message' => 'Claims retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Claims API index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve claims'
            ], 500);
        }
    }

    /**
     * Store a newly created claim.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'claim_type_id' => 'required|integer|exists:claim_types,id',
                'amount' => 'required|numeric|min:0|max:999999.99',
                'claim_date' => 'required|date',
                'description' => 'required|string|max:1000',
                'business_purpose' => 'nullable|string|max:500',
                'receipt_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
                'notes' => 'nullable|string|max:1000'
            ]);

            // Handle file upload if present
            $receiptPath = null;
            if ($request->hasFile('receipt_path')) {
                $file = $request->file('receipt_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $receiptPath = $file->storeAs('claims/receipts', $filename, 'public');
            }

            $claim = Claim::create([
                'employee_id' => $validated['employee_id'],
                'claim_type_id' => $validated['claim_type_id'],
                'amount' => $validated['amount'],
                'claim_date' => $validated['claim_date'],
                'description' => $validated['description'],
                'business_purpose' => $validated['business_purpose'] ?? null,
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null
            ]);

            // Load relationships for response
            $claim->load(['employee', 'claimType']);

            return response()->json([
                'status' => 'success',
                'data' => $claim,
                'message' => 'Claim submitted successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Claims API store error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create claim'
            ], 500);
        }
    }

    /**
     * Display the specified claim.
     */
    public function show($id)
    {
        try {
            $claim = Claim::with(['employee', 'claimType', 'approver'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $claim,
                'message' => 'Claim retrieved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Claim not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Claims API show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve claim'
            ], 500);
        }
    }

    /**
     * Update the specified claim.
     */
    public function update(Request $request, $id)
    {
        try {
            $claim = Claim::findOrFail($id);

            // Only allow updates if claim is pending
            if ($claim->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update claim that is not pending'
                ], 400);
            }

            $validated = $request->validate([
                'claim_type_id' => 'sometimes|integer|exists:claim_types,id',
                'amount' => 'sometimes|numeric|min:0|max:999999.99',
                'claim_date' => 'sometimes|date',
                'description' => 'sometimes|string|max:1000',
                'business_purpose' => 'sometimes|string|max:500',
                'receipt_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'notes' => 'sometimes|string|max:1000'
            ]);

            // Handle file upload if present
            if ($request->hasFile('receipt_path')) {
                // Delete old file if exists
                if ($claim->receipt_path && Storage::disk('public')->exists($claim->receipt_path)) {
                    Storage::disk('public')->delete($claim->receipt_path);
                }

                $file = $request->file('receipt_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $validated['receipt_path'] = $file->storeAs('claims/receipts', $filename, 'public');
            }

            $claim->update($validated);
            $claim->load(['employee', 'claimType']);

            return response()->json([
                'status' => 'success',
                'data' => $claim,
                'message' => 'Claim updated successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Claim not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Claims API update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update claim'
            ], 500);
        }
    }

    /**
     * Approve a claim.
     */
    public function approve(Request $request, $id)
    {
        try {
            $claim = Claim::findOrFail($id);

            if ($claim->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only approve pending claims'
                ], 400);
            }

            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000'
            ]);

            $claim->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1, // Default to admin if no auth
                'approved_at' => now(),
                'notes' => $validated['notes'] ?? $claim->notes
            ]);

            $claim->load(['employee', 'claimType', 'approver']);

            return response()->json([
                'status' => 'success',
                'data' => $claim,
                'message' => 'Claim approved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Claim not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Claims API approve error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve claim'
            ], 500);
        }
    }

    /**
     * Reject a claim.
     */
    public function reject(Request $request, $id)
    {
        try {
            $claim = Claim::findOrFail($id);

            if ($claim->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only reject pending claims'
                ], 400);
            }

            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:1000',
                'notes' => 'nullable|string|max:1000'
            ]);

            $claim->update([
                'status' => 'rejected',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'notes' => $validated['notes'] ?? $claim->notes
            ]);

            $claim->load(['employee', 'claimType', 'approver']);

            return response()->json([
                'status' => 'success',
                'data' => $claim,
                'message' => 'Claim rejected successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Claim not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Claims API reject error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject claim'
            ], 500);
        }
    }

    /**
     * Remove the specified claim.
     */
    public function destroy($id)
    {
        try {
            $claim = Claim::findOrFail($id);

            // Delete associated file if exists
            if ($claim->receipt_path && Storage::disk('public')->exists($claim->receipt_path)) {
                Storage::disk('public')->delete($claim->receipt_path);
            }

            $claim->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Claim deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Claim not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Claims API destroy error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete claim'
            ], 500);
        }
    }

    /**
     * Get claims statistics.
     */
    public function statistics(Request $request)
    {
        try {
            $employeeId = $request->get('employee_id');
            $query = Claim::query();

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            $stats = [
                'total_claims' => $query->count(),
                'pending_claims' => $query->where('status', 'pending')->count(),
                'approved_claims' => $query->where('status', 'approved')->count(),
                'rejected_claims' => $query->where('status', 'rejected')->count(),
                'total_amount' => $query->sum('amount'),
                'approved_amount' => $query->where('status', 'approved')->sum('amount'),
                'pending_amount' => $query->where('status', 'pending')->sum('amount')
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Claims API statistics error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }
}
