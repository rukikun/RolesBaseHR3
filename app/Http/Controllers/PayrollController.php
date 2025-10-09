<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\ValidatedAttachment;

class PayrollController extends Controller
{
    /**
     * Display the payroll management page
     */
    public function index()
    {
        try {
            // Get payroll items
            $payrollItems = collect([]);
            
            try {
                // Try using Eloquent first
                $payrollItems = Payroll::with(['employee'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($payroll) {
                        // Add computed properties for blade compatibility
                        $payroll->employee_name = ($payroll->employee->first_name ?? 'Employee') . ' ' . ($payroll->employee->last_name ?? 'Unknown');
                        return $payroll;
                    });
                
                Log::info('Eloquent - Retrieved ' . $payrollItems->count() . ' payroll items');
            } catch (\Exception $e) {
                Log::warning('Eloquent failed, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw PDO queries
                try {
                    $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Get payroll items with employee data
                    $stmt = $pdo->query("
                        SELECT p.id, p.employee_id, p.timesheet_id, p.department, p.week_period, 
                               p.total_hours, p.overtime_hours, p.hourly_rate, p.overtime_rate,
                               p.regular_amount, p.overtime_amount, p.total_amount, p.status,
                               p.processed_at, p.created_at, p.updated_at,
                               COALESCE(e.first_name, 'Employee') as first_name, 
                               COALESCE(e.last_name, CONCAT('ID:', p.employee_id)) as last_name,
                               CONCAT(COALESCE(e.first_name, 'Employee'), ' ', COALESCE(e.last_name, CONCAT('ID:', p.employee_id))) as employee_name
                        FROM payroll p
                        LEFT JOIN employees e ON p.employee_id = e.id
                        ORDER BY p.created_at DESC
                    ");
                    $payrollData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $payrollItems = collect($payrollData);
                    
                    Log::info('Raw PDO - Retrieved ' . count($payrollData) . ' payroll items');
                } catch (\Exception $e2) {
                    Log::error('Raw PDO also failed: ' . $e2->getMessage());
                    $payrollItems = collect([]);
                }
            }
            
            // Get validated attachments
            $validatedAttachments = collect([]);
            
            try {
                // Try using Eloquent first
                $validatedAttachments = ValidatedAttachment::orderBy('created_at', 'desc')->get();
                
                Log::info('Eloquent - Retrieved ' . $validatedAttachments->count() . ' validated attachments');
            } catch (\Exception $e) {
                Log::warning('Eloquent failed for validated attachments, falling back to raw queries: ' . $e->getMessage());
                
                // Fallback to raw PDO queries
                try {
                    $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Get validated attachments
                    $stmt = $pdo->query("
                        SELECT * FROM validated_attachments 
                        ORDER BY created_at DESC
                    ");
                    $validatedData = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $validatedAttachments = collect($validatedData);
                    
                    Log::info('Raw PDO - Retrieved ' . count($validatedData) . ' validated attachments');
                } catch (\Exception $e2) {
                    Log::error('Raw PDO also failed for validated attachments: ' . $e2->getMessage());
                    $validatedAttachments = collect([]);
                }
            }
                
            return view('payroll.management', compact('payrollItems', 'validatedAttachments'));
            
        } catch (\Exception $e) {
            Log::error('Payroll index error: ' . $e->getMessage());
            
            return view('payroll.management', [
                'payrollItems' => collect([]),
                'validatedAttachments' => collect([])
            ])->with('error', 'Error loading payroll data: ' . $e->getMessage());
        }
    }

    /**
     * Send timesheet to payroll
     */
    public function sendToPayroll(Request $request)
    {
        try {
            $timesheetId = $request->input('timesheet_id');
            $employeeId = $request->input('employee_id');
            $department = $request->input('department');
            $weekPeriod = $request->input('week_period');
            $totalHours = $request->input('total_hours', 0);
            $overtimeHours = $request->input('overtime_hours', 0);
            
            // Calculate payroll amounts (you can customize these rates)
            $hourlyRate = 25.00; // Default hourly rate
            $overtimeRate = 37.50; // 1.5x overtime rate
            
            $regularAmount = $totalHours * $hourlyRate;
            $overtimeAmount = $overtimeHours * $overtimeRate;
            $totalAmount = $regularAmount + $overtimeAmount;
            
            // Try Eloquent first
            try {
                $payroll = Payroll::create([
                    'timesheet_id' => $timesheetId,
                    'employee_id' => $employeeId,
                    'department' => $department,
                    'week_period' => $weekPeriod,
                    'total_hours' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'hourly_rate' => $hourlyRate,
                    'overtime_rate' => $overtimeRate,
                    'regular_amount' => $regularAmount,
                    'overtime_amount' => $overtimeAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Timesheet sent to payroll successfully!',
                    'payroll_id' => $payroll->id
                ]);
                
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    INSERT INTO payroll (timesheet_id, employee_id, department, week_period, 
                                       total_hours, overtime_hours, hourly_rate, overtime_rate,
                                       regular_amount, overtime_amount, total_amount, status, 
                                       created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
                ");
                
                $stmt->execute([
                    $timesheetId, $employeeId, $department, $weekPeriod,
                    $totalHours, $overtimeHours, $hourlyRate, $overtimeRate,
                    $regularAmount, $overtimeAmount, $totalAmount
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Timesheet sent to payroll successfully!',
                    'payroll_id' => $pdo->lastInsertId()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Send to payroll error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending timesheet to payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payroll item
     */
    public function process($id)
    {
        try {
            // Try Eloquent first
            try {
                $payroll = Payroll::findOrFail($id);
                
                if ($payroll->status !== 'pending') {
                    return redirect()->route('payroll-management')
                        ->with('error', 'Only pending payroll items can be processed.');
                }

                $payroll->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'updated_at' => now()
                ]);

                return redirect()->route('payroll-management')
                    ->with('success', 'Payroll item processed successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE payroll 
                    SET status = 'processed', processed_at = NOW(), updated_at = NOW() 
                    WHERE id = ? AND status = 'pending'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Payroll item processed successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Payroll process error: ' . $e->getMessage());
            return redirect()->route('payroll-management')
                ->with('error', 'Error processing payroll item: ' . $e->getMessage());
        }
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid($id)
    {
        try {
            // Try Eloquent first
            try {
                $payroll = Payroll::findOrFail($id);
                
                if ($payroll->status !== 'processed') {
                    return redirect()->route('payroll-management')
                        ->with('error', 'Only processed payroll items can be marked as paid.');
                }

                $payroll->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'updated_at' => now()
                ]);

                return redirect()->route('payroll-management')
                    ->with('success', 'Payroll item marked as paid successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE payroll 
                    SET status = 'paid', paid_at = NOW(), updated_at = NOW() 
                    WHERE id = ? AND status = 'processed'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Payroll item marked as paid successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Payroll mark paid error: ' . $e->getMessage());
            return redirect()->route('payroll-management')
                ->with('error', 'Error marking payroll as paid: ' . $e->getMessage());
        }
    }

    /**
     * Delete payroll item
     */
    public function destroy($id)
    {
        try {
            // Try Eloquent first
            try {
                $payroll = Payroll::findOrFail($id);
                $payroll->delete();

                return redirect()->route('payroll-management')
                    ->with('success', 'Payroll item deleted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("DELETE FROM payroll WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Payroll item deleted successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Payroll delete error: ' . $e->getMessage());
            return redirect()->route('payroll-management')
                ->with('error', 'Error deleting payroll item: ' . $e->getMessage());
        }
    }

    /**
     * Process validated attachment
     */
    public function processAttachment($id)
    {
        try {
            // Try Eloquent first
            try {
                $attachment = ValidatedAttachment::findOrFail($id);
                
                if ($attachment->status !== 'validated') {
                    return redirect()->route('payroll-management')
                        ->with('error', 'Only validated attachments can be processed.');
                }

                $attachment->update([
                    'status' => 'sent_to_payroll',
                    'sent_to_payroll_at' => now(),
                    'updated_at' => now()
                ]);

                return redirect()->route('payroll-management')
                    ->with('success', 'Validated attachment processed successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE validated_attachments 
                    SET status = 'sent_to_payroll', sent_to_payroll_at = NOW(), updated_at = NOW() 
                    WHERE id = ? AND status = 'validated'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Validated attachment processed successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Process attachment error: ' . $e->getMessage());
            return redirect()->route('payroll-management')
                ->with('error', 'Error processing attachment: ' . $e->getMessage());
        }
    }

    /**
     * Mark validated attachment as paid
     */
    public function markAttachmentPaid($id)
    {
        try {
            // Try Eloquent first
            try {
                $attachment = ValidatedAttachment::findOrFail($id);
                
                if ($attachment->status !== 'sent_to_payroll') {
                    return redirect()->route('payroll-management')
                        ->with('error', 'Only attachments sent to payroll can be marked as processed.');
                }

                $attachment->update([
                    'status' => 'processed',
                    'updated_at' => now()
                ]);

                return redirect()->route('payroll-management')
                    ->with('success', 'Validated attachment marked as processed successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("
                    UPDATE validated_attachments 
                    SET status = 'processed', updated_at = NOW() 
                    WHERE id = ? AND status = 'sent_to_payroll'
                ");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Validated attachment marked as processed successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Mark attachment paid error: ' . $e->getMessage());
            return redirect()->route('payroll-management')
                ->with('error', 'Error marking attachment as processed: ' . $e->getMessage());
        }
    }

    /**
     * Delete validated attachment
     */
    public function deleteAttachment($id)
    {
        try {
            // Try Eloquent first
            try {
                $attachment = ValidatedAttachment::findOrFail($id);
                $attachment->delete();

                return redirect()->route('payroll-management')
                    ->with('success', 'Validated attachment deleted successfully!');
                    
            } catch (\Exception $e) {
                // Fallback to raw PDO
                $pdo = new \PDO('mysql:host=localhost;dbname=hr3systemdb', 'root', '');
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("DELETE FROM validated_attachments WHERE id = ?");
                $stmt->execute([$id]);
                
                return redirect()->route('payroll-management')
                    ->with('success', 'Validated attachment deleted successfully!');
            }
            
        } catch (\Exception $e) {
            Log::error('Delete validated attachment error: ' . $e->getMessage());
            return redirect()->route('payroll-management')
                ->with('error', 'Error deleting validated attachment: ' . $e->getMessage());
        }
    }
}
