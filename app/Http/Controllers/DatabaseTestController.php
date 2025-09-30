<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseTestController extends Controller
{
    /**
     * Test database connectivity and table existence
     */
    public function testConnection()
    {
        try {
            $results = [];
            
            // Test basic connection
            DB::connection()->getPdo();
            $results['connection'] = 'SUCCESS';
            
            // Test each table exists and has data
            $tables = [
                'users',
                'employees', 
                'time_entries',
                'shift_types',
                'shifts',
                'shift_requests',
                'leave_types',
                'leave_balances',
                'leave_requests',
                'claim_types',
                'claims'
            ];
            
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $results['tables'][$table] = [
                        'exists' => true,
                        'count' => $count,
                        'status' => 'OK'
                    ];
                } catch (\Exception $e) {
                    $results['tables'][$table] = [
                        'exists' => false,
                        'error' => $e->getMessage(),
                        'status' => 'ERROR'
                    ];
                }
            }
            
            // Test sample queries
            try {
                $employeeCount = DB::table('employees')->where('status', 'active')->count();
                $timesheetCount = DB::table('time_entries')->count();
                $leaveCount = DB::table('leave_requests')->count();
                $claimCount = DB::table('claims')->count();
                
                $results['sample_data'] = [
                    'active_employees' => $employeeCount,
                    'total_timesheets' => $timesheetCount,
                    'leave_requests' => $leaveCount,
                    'claims' => $claimCount
                ];
            } catch (\Exception $e) {
                $results['sample_data'] = ['error' => $e->getMessage()];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Database connection test completed',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * Test CRUD operations on all modules
     */
    public function testCrudOperations()
    {
        try {
            $results = [];
            
            // Test Employee CRUD
            try {
                $employee = DB::table('employees')->insertGetId([
                    'employee_number' => 'TEST001',
                    'first_name' => 'Test',
                    'last_name' => 'Employee',
                    'email' => 'test@example.com',
                    'position' => 'Test Position',
                    'department' => 'Test Department',
                    'hire_date' => now()->format('Y-m-d'),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('employees')->where('id', $employee)->update([
                    'first_name' => 'Updated Test',
                    'updated_at' => now()
                ]);
                
                $updated = DB::table('employees')->where('id', $employee)->first();
                
                DB::table('employees')->where('id', $employee)->delete();
                
                $results['employee_crud'] = [
                    'create' => 'SUCCESS',
                    'read' => 'SUCCESS',
                    'update' => $updated->first_name === 'Updated Test' ? 'SUCCESS' : 'FAILED',
                    'delete' => 'SUCCESS'
                ];
            } catch (\Exception $e) {
                $results['employee_crud'] = ['error' => $e->getMessage()];
            }
            
            // Test Timesheet CRUD
            try {
                $timesheet = DB::table('time_entries')->insertGetId([
                    'employee_id' => 1,
                    'work_date' => now()->format('Y-m-d'),
                    'hours_worked' => 8.0,
                    'overtime_hours' => 0.0,
                    'description' => 'Test timesheet',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('time_entries')->where('id', $timesheet)->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);
                
                DB::table('time_entries')->where('id', $timesheet)->delete();
                
                $results['timesheet_crud'] = [
                    'create' => 'SUCCESS',
                    'update' => 'SUCCESS',
                    'delete' => 'SUCCESS'
                ];
            } catch (\Exception $e) {
                $results['timesheet_crud'] = ['error' => $e->getMessage()];
            }
            
            // Test Leave Request CRUD
            try {
                $leave = DB::table('leave_requests')->insertGetId([
                    'employee_id' => 1,
                    'leave_type_id' => 1,
                    'start_date' => now()->addDays(7)->format('Y-m-d'),
                    'end_date' => now()->addDays(9)->format('Y-m-d'),
                    'days_requested' => 3.0,
                    'reason' => 'Test leave request',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('leave_requests')->where('id', $leave)->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);
                
                DB::table('leave_requests')->where('id', $leave)->delete();
                
                $results['leave_crud'] = [
                    'create' => 'SUCCESS',
                    'update' => 'SUCCESS',
                    'delete' => 'SUCCESS'
                ];
            } catch (\Exception $e) {
                $results['leave_crud'] = ['error' => $e->getMessage()];
            }
            
            // Test Claim CRUD
            try {
                $claim = DB::table('claims')->insertGetId([
                    'employee_id' => 1,
                    'claim_type_id' => 1,
                    'amount' => 100.00,
                    'submitted_date' => now()->format('Y-m-d'),
                    'description' => 'Test claim',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                DB::table('claims')->where('id', $claim)->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);
                
                DB::table('claims')->where('id', $claim)->delete();
                
                $results['claim_crud'] = [
                    'create' => 'SUCCESS',
                    'update' => 'SUCCESS',
                    'delete' => 'SUCCESS'
                ];
            } catch (\Exception $e) {
                $results['claim_crud'] = ['error' => $e->getMessage()];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'CRUD operations test completed',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CRUD test failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * Get database statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'employees' => [
                    'total' => DB::table('employees')->count(),
                    'active' => DB::table('employees')->where('status', 'active')->count(),
                    'inactive' => DB::table('employees')->where('status', 'inactive')->count(),
                    'terminated' => DB::table('employees')->where('status', 'terminated')->count()
                ],
                'timesheets' => [
                    'total' => DB::table('time_entries')->count(),
                    'pending' => DB::table('time_entries')->where('status', 'pending')->count(),
                    'approved' => DB::table('time_entries')->where('status', 'approved')->count(),
                    'rejected' => DB::table('time_entries')->where('status', 'rejected')->count(),
                    'total_hours' => DB::table('time_entries')->sum('hours_worked')
                ],
                'leaves' => [
                    'total' => DB::table('leave_requests')->count(),
                    'pending' => DB::table('leave_requests')->where('status', 'pending')->count(),
                    'approved' => DB::table('leave_requests')->where('status', 'approved')->count(),
                    'rejected' => DB::table('leave_requests')->where('status', 'rejected')->count()
                ],
                'claims' => [
                    'total' => DB::table('claims')->count(),
                    'pending' => DB::table('claims')->where('status', 'pending')->count(),
                    'approved' => DB::table('claims')->where('status', 'approved')->count(),
                    'rejected' => DB::table('claims')->where('status', 'rejected')->count(),
                    'paid' => DB::table('claims')->where('status', 'paid')->count(),
                    'total_amount' => DB::table('claims')->sum('amount')
                ],
                'shifts' => [
                    'total' => DB::table('shifts')->count(),
                    'scheduled' => DB::table('shifts')->where('status', 'scheduled')->count(),
                    'completed' => DB::table('shifts')->where('status', 'completed')->count(),
                    'cancelled' => DB::table('shifts')->where('status', 'cancelled')->count()
                ]
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Database statistics retrieved',
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
