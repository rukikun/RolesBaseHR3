<?php

namespace App\Http\Controllers;

use App\Services\ClockifyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClockifyController extends Controller
{
    protected $clockifyService;

    public function __construct(ClockifyService $clockifyService)
    {
        $this->clockifyService = $clockifyService;
        $this->middleware('auth');
    }

    /**
     * Test Clockify connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->clockifyService->testConnection();
            
            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Clockify connection successful' : 'Clockify connection failed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing Clockify connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user from Clockify
     */
    public function getCurrentUser(): JsonResponse
    {
        try {
            $user = $this->clockifyService->getCurrentUser();
            
            if ($user) {
                return response()->json([
                    'success' => true,
                    'data' => $user
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get current user from Clockify'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting current user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start time tracking
     */
    public function startTimer(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'description' => 'nullable|string|max:255',
                'project_id' => 'nullable|string',
                'task_id' => 'nullable|string',
            ]);

            // Get current user's Clockify ID (you might want to store this in user profile)
            $currentUser = $this->clockifyService->getCurrentUser();
            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get current user from Clockify'
                ], 400);
            }

            $timeEntry = $this->clockifyService->startTimeEntry(
                $currentUser['id'],
                $request->input('description'),
                $request->input('project_id'),
                $request->input('task_id')
            );

            if ($timeEntry) {
                return response()->json([
                    'success' => true,
                    'data' => $timeEntry,
                    'message' => 'Timer started successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to start timer'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error starting timer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop time tracking
     */
    public function stopTimer(): JsonResponse
    {
        try {
            // Get current user's Clockify ID
            $currentUser = $this->clockifyService->getCurrentUser();
            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get current user from Clockify'
                ], 400);
            }

            $timeEntry = $this->clockifyService->stopTimeEntry($currentUser['id']);

            if ($timeEntry) {
                return response()->json([
                    'success' => true,
                    'data' => $timeEntry,
                    'message' => 'Timer stopped successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to stop timer'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error stopping timer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get time entries
     */
    public function getTimeEntries(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'page_size' => 'nullable|integer|min:1|max:200',
            ]);

            // Get current user's Clockify ID
            $currentUser = $this->clockifyService->getCurrentUser();
            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get current user from Clockify'
                ], 400);
            }

            $timeEntries = $this->clockifyService->getTimeEntries(
                $currentUser['id'],
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('page', 1),
                $request->input('page_size', 50)
            );

            if ($timeEntries !== null) {
                return response()->json([
                    'success' => true,
                    'data' => $timeEntries
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get time entries'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting time entries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get projects
     */
    public function getProjects(): JsonResponse
    {
        try {
            $projects = $this->clockifyService->getProjects();

            if ($projects !== null) {
                return response()->json([
                    'success' => true,
                    'data' => $projects
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get projects'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new project
     */
    public function createProject(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'client_id' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            ]);

            $project = $this->clockifyService->createProject(
                $request->input('name'),
                $request->input('client_id'),
                $request->input('color', '#FF5722')
            );

            if ($project) {
                return response()->json([
                    'success' => true,
                    'data' => $project,
                    'message' => 'Project created successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create project'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workspace users
     */
    public function getWorkspaceUsers(): JsonResponse
    {
        try {
            $users = $this->clockifyService->getWorkspaceUsers();

            if ($users !== null) {
                return response()->json([
                    'success' => true,
                    'data' => $users
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get workspace users'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting workspace users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed time report
     */
    public function getDetailedReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'string',
            ]);

            $report = $this->clockifyService->getDetailedReport(
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('user_ids')
            );

            if ($report !== null) {
                return response()->json([
                    'success' => true,
                    'data' => $report
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get detailed report'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting detailed report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workspaces
     */
    public function getWorkspaces(): JsonResponse
    {
        try {
            $workspaces = $this->clockifyService->getWorkspaces();

            if ($workspaces !== null) {
                return response()->json([
                    'success' => true,
                    'data' => $workspaces
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get workspaces'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting workspaces: ' . $e->getMessage()
            ], 500);
        }
    }
}
