<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * Leave Request API Controller - Approve/Reject Functions Only
 */
class LeaveRequestController extends Controller
{

    /**
     * Approve a leave request
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only approve pending requests'
                ], 400);
            }

            $validated = $request->validate([
                'comments' => 'nullable|string|max:1000'
            ]);

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'comments' => $validated['comments'] ?? $leaveRequest->comments
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request approved successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        }
    }

    /**
     * Reject a leave request
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only reject pending requests'
                ], 400);
            }

            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:1000',
                'comments' => 'nullable|string|max:1000'
            ]);

            $leaveRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'comments' => $validated['comments'] ?? $leaveRequest->comments
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request rejected successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leave request not found'
            ], 404);
        }
    }

    /**
     * ========================================
     * ENDPOINT FOR RECEIVING DATA FROM OTHER SYSTEMS
     * ========================================
     * 
     * URL: POST /api/leave-requests/receive
     * Purpose: Receive single leave request data from external systems
     * 
     * This is the main endpoint where other systems can send leave request data
     * to be processed and stored in your HR system.
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'leave_type_id' => 'required|integer|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'days_requested' => 'required|numeric|min:0.5',
                'reason' => 'required|string|max:1000',
                'comments' => 'nullable|string|max:1000',
                'is_emergency' => 'nullable|boolean',
                'external_reference' => 'nullable|string|max:100',
                'external_system' => 'nullable|string|max:50'
            ]);

            // Create leave request from external data
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'days_requested' => $validated['days_requested'],
                'reason' => $validated['reason'],
                'comments' => $validated['comments'] ?? null,
                'status' => 'pending',
                'is_emergency' => $validated['is_emergency'] ?? false,
                'external_reference' => $validated['external_reference'] ?? null
            ]);

            Log::info('Leave request received from external system', [
                'leave_request_id' => $leaveRequest->id,
                'external_system' => $validated['external_system'] ?? 'unknown',
                'external_reference' => $validated['external_reference'] ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'leave_request_id' => $leaveRequest->id,
                    'status' => $leaveRequest->status,
                    'received_at' => now()->toISOString()
                ],
                'message' => 'Leave request data received successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid data received',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error receiving leave request data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process received data'
            ], 500);
        }
    }

}