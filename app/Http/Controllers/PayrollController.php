<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayrollItem;
use App\Models\ValidatedAttachment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display payroll management page
     */
    public function index()
    {
        try {
            // Get payroll items
            $payrollItems = PayrollItem::orderBy('created_at', 'desc')->get();
            
            // Get validated attachments
            $validatedAttachments = ValidatedAttachment::orderBy('created_at', 'desc')->get();
            
            return view('payroll.management', compact('payrollItems', 'validatedAttachments'));
            
        } catch (\Exception $e) {
            // Fallback with empty collections if there's an error
            $payrollItems = collect();
            $validatedAttachments = collect();
            
            return view('payroll.management', compact('payrollItems', 'validatedAttachments'))
                ->with('error', 'Error loading payroll data: ' . $e->getMessage());
        }
    }

    /**
     * Get payroll items via API
     */
    public function getPayrollItems(Request $request)
    {
        try {
            $query = PayrollItem::query();
            
            // Filter by status if provided
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            $payrollItems = $query->orderBy('created_at', 'desc')->get();
            
            $formattedItems = $payrollItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'employee_name' => $item->employee_name,
                    'department' => $item->department,
                    'week_period' => $item->week_period,
                    'total_hours' => $item->total_hours,
                    'overtime_hours' => $item->overtime_hours,
                    'total_amount' => $item->total_amount,
                    'formatted_amount' => $item->formatted_total_amount,
                    'status' => $item->status,
                    'processed_date' => $item->processed_date ? 
                        $item->processed_date->format('M d, Y') : 
                        'Not processed'
                ];
            });
            
            return response()->json([
                'success' => true,
                'payroll_items' => $formattedItems
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payroll items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a payroll item
     */
    public function processPayrollItem($id)
    {
        try {
            $payrollItem = PayrollItem::findOrFail($id);
            
            if ($payrollItem->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payroll items can be processed'
                ], 400);
            }
            
            $payrollItem->update([
                'status' => 'processed',
                'processed_date' => now(),
                'processed_by' => auth()->id() ?? 1
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payroll item processed successfully'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payroll item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payroll item
     */
    public function deletePayrollItem($id)
    {
        try {
            $payrollItem = PayrollItem::findOrFail($id);
            $payrollItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Payroll item deleted successfully'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payroll item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a payroll item (web route)
     */
    public function process($id)
    {
        try {
            $payrollItem = PayrollItem::findOrFail($id);
            
            if ($payrollItem->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending payroll items can be processed');
            }
            
            $payrollItem->update([
                'status' => 'processed',
                'processed_date' => now(),
                'processed_by' => auth()->id() ?? 1
            ]);
            
            return redirect()->back()->with('success', 'Payroll item processed successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing payroll item: ' . $e->getMessage());
        }
    }

    /**
     * Mark payroll item as paid
     */
    public function markPaid($id)
    {
        try {
            $payrollItem = PayrollItem::findOrFail($id);
            
            if ($payrollItem->status !== 'processed') {
                return redirect()->back()->with('error', 'Only processed payroll items can be marked as paid');
            }
            
            $payrollItem->update([
                'status' => 'paid',
                'paid_date' => now(),
                'paid_by' => auth()->id() ?? 1
            ]);
            
            return redirect()->back()->with('success', 'Payroll item marked as paid successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error marking payroll as paid: ' . $e->getMessage());
        }
    }

    /**
     * Delete a payroll item (web route)
     */
    public function destroy($id)
    {
        try {
            $payrollItem = PayrollItem::findOrFail($id);
            $payrollItem->delete();
            
            return redirect()->back()->with('success', 'Payroll item deleted successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting payroll item: ' . $e->getMessage());
        }
    }

    /**
     * Process a validated attachment
     */
    public function processAttachment($id)
    {
        try {
            $attachment = ValidatedAttachment::findOrFail($id);
            
            if ($attachment->status !== 'validated') {
                return redirect()->back()->with('error', 'Only validated attachments can be processed');
            }
            
            $attachment->update([
                'status' => 'sent_to_payroll',
                'sent_to_payroll_at' => now()
            ]);
            
            return redirect()->back()->with('success', 'Validated attachment processed successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing attachment: ' . $e->getMessage());
        }
    }

    /**
     * Mark validated attachment as paid
     */
    public function markAttachmentPaid($id)
    {
        try {
            $attachment = ValidatedAttachment::findOrFail($id);
            
            if ($attachment->status !== 'sent_to_payroll') {
                return redirect()->back()->with('error', 'Only attachments sent to payroll can be marked as processed');
            }
            
            $attachment->update([
                'status' => 'processed'
            ]);
            
            return redirect()->back()->with('success', 'Validated attachment marked as processed successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error marking attachment as processed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a validated attachment
     */
    public function deleteAttachment($id)
    {
        try {
            $attachment = ValidatedAttachment::findOrFail($id);
            $attachment->delete();
            
            return redirect()->back()->with('success', 'Validated attachment deleted successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting validated attachment: ' . $e->getMessage());
        }
    }

    /**
     * Send timesheet to payroll (alternative route)
     */
    public function sendToPayroll(Request $request)
    {
        try {
            // Get timesheet ID from request
            $timesheetId = $request->input('timesheet_id');
            
            if (!$timesheetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timesheet ID is required'
                ], 400);
            }

            // Use the TimesheetController method
            $timesheetController = new \App\Http\Controllers\TimesheetController();
            return $timesheetController->sendToPayroll($timesheetId);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send timesheet to payroll: ' . $e->getMessage()
            ], 500);
        }
    }
}