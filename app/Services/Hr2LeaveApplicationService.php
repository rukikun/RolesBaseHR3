<?php

namespace App\Services;

use App\Models\Hr2LeaveApplication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Hr2LeaveApplicationService
{
    /**
     * The HR2 API base URL.
     */
    protected string $apiBaseUrl = 'https://hr2.jetlougetravels-ph.com/api';

    /**
     * The endpoint for leave applications.
     */
    protected string $leaveApplicationsEndpoint = '/leave-applications';

    /**
     * Fetch leave applications from the HR2 API.
     *
     * @return Collection
     */
    public function fetchFromApi(): Collection
    {
        try {
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false, // Disable SSL verification if needed
                ])
                ->get($this->apiBaseUrl . $this->leaveApplicationsEndpoint);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('HR2 Leave Applications API response received', [
                    'count' => is_array($data) ? count($data) : 0
                ]);
                return collect($data);
            }

            Log::error('HR2 Leave Applications API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return collect([]);
        } catch (\Exception $e) {
            Log::error('HR2 Leave Applications API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return collect([]);
        }
    }

    /**
     * Sync leave applications from the HR2 API to the local database.
     *
     * @return array Returns sync statistics
     */
    public function syncFromApi(): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'total' => 0,
        ];

        $apiData = $this->fetchFromApi();
        $stats['total'] = $apiData->count();

        foreach ($apiData as $item) {
            try {
                $hr2Id = $item['id'] ?? null;
                
                if (!$hr2Id) {
                    $stats['failed']++;
                    continue;
                }

                $leaveApplication = Hr2LeaveApplication::updateOrCreate(
                    ['hr2_id' => $hr2Id],
                    [
                        'employee_id' => $item['employee_id'] ?? null,
                        'leave_id' => $item['leave_id'] ?? null,
                        'application_date' => isset($item['application_date']) ? Carbon::parse($item['application_date']) : null,
                        'leave_type' => $item['leave_type'] ?? null,
                        'leave_days' => $item['leave_days'] ?? 0,
                        'days_requested' => $item['days_requested'] ?? 0,
                        'status' => $item['status'] ?? 'Pending',
                        'reason' => $item['reason'] ?? null,
                        'start_date' => isset($item['start_date']) ? Carbon::parse($item['start_date']) : null,
                        'end_date' => isset($item['end_date']) ? Carbon::parse($item['end_date']) : null,
                        'contact_info' => $item['contact_info'] ?? null,
                        'applied_date' => isset($item['applied_date']) ? Carbon::parse($item['applied_date']) : null,
                        'approved_by' => $item['approved_by'] ?? null,
                        'approved_date' => isset($item['approved_date']) ? Carbon::parse($item['approved_date']) : null,
                        'remarks' => $item['remarks'] ?? null,
                        'hr2_created_at' => isset($item['created_at']) ? Carbon::parse($item['created_at']) : null,
                        'hr2_updated_at' => isset($item['updated_at']) ? Carbon::parse($item['updated_at']) : null,
                    ]
                );

                if ($leaveApplication->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync HR2 leave application', [
                    'hr2_id' => $item['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $stats['failed']++;
            }
        }

        Log::info('HR2 Leave Applications sync completed', $stats);

        return $stats;
    }

    /**
     * Get all leave applications from the local database.
     *
     * @param string|null $status Filter by status
     * @return Collection
     */
    public function getAll(?string $status = null): Collection
    {
        $query = Hr2LeaveApplication::orderBy('application_date', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get leave applications directly from the API without caching.
     *
     * @return Collection
     */
    public function getFromApiDirect(): Collection
    {
        return $this->fetchFromApi()->map(function ($item) {
            return (object) [
                'id' => $item['id'] ?? null,
                'employee_id' => $item['employee_id'] ?? 'N/A',
                'leave_id' => $item['leave_id'] ?? 'N/A',
                'application_date' => $item['application_date'] ?? null,
                'leave_type' => $item['leave_type'] ?? 'Unknown',
                'leave_days' => $item['leave_days'] ?? 0,
                'days_requested' => $item['days_requested'] ?? 0,
                'status' => $item['status'] ?? 'Pending',
                'reason' => $item['reason'] ?? 'N/A',
                'start_date' => $item['start_date'] ?? null,
                'end_date' => $item['end_date'] ?? null,
                'contact_info' => $item['contact_info'] ?? null,
                'applied_date' => $item['applied_date'] ?? null,
                'approved_by' => $item['approved_by'] ?? null,
                'approved_date' => $item['approved_date'] ?? null,
                'remarks' => $item['remarks'] ?? null,
                'status_badge_class' => match(strtolower($item['status'] ?? 'pending')) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'rejected', 'declined' => 'danger',
                    'cancelled' => 'secondary',
                    default => 'secondary'
                },
            ];
        });
    }

    /**
     * Get pending leave applications count.
     *
     * @return int
     */
    public function getPendingCount(): int
    {
        return Hr2LeaveApplication::pending()->count();
    }

    /**
     * Update leave application status via PATCH request to HR2 API.
     *
     * @param string|int $id The leave application ID
     * @param string $status The new status ('Approved' or 'Rejected')
     * @param string|null $remarks Optional remarks for the status update
     * @return array Returns ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function updateLeaveStatus($id, string $status, ?string $remarks = null): array
    {
        try {
            $url = $this->apiBaseUrl . $this->leaveApplicationsEndpoint . '/' . $id;
            
            $payload = [
                'status' => $status,
            ];
            
            if ($remarks) {
                $payload['remarks'] = $remarks;
            }
            
            Log::info('HR2 Leave Application PATCH request', [
                'url' => $url,
                'payload' => $payload
            ]);

            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false, // Disable SSL verification if needed
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->patch($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('HR2 Leave Application status updated successfully', [
                    'id' => $id,
                    'status' => $status,
                    'response' => $data
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Leave application status updated successfully',
                    'data' => $data
                ];
            }

            Log::error('HR2 Leave Application PATCH request failed', [
                'id' => $id,
                'status_code' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update leave status: ' . $response->body(),
                'data' => null
            ];
        } catch (\Exception $e) {
            Log::error('HR2 Leave Application PATCH exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error updating leave status: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Approve a leave application via PATCH request.
     *
     * @param string|int $id The leave application ID
     * @param string|null $remarks Optional remarks
     * @return array
     */
    public function approveLeave($id, ?string $remarks = null): array
    {
        return $this->updateLeaveStatus($id, 'Approved', $remarks);
    }

    /**
     * Reject a leave application via PATCH request.
     *
     * @param string|int $id The leave application ID
     * @param string|null $remarks Optional remarks (reason for rejection)
     * @return array
     */
    public function rejectLeave($id, ?string $remarks = null): array
    {
        return $this->updateLeaveStatus($id, 'Rejected', $remarks);
    }
}
