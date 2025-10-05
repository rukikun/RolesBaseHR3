<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\DatabaseConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftTypesController extends Controller
{
    use DatabaseConnectionTrait;
    public function index()
    {
        try {
            // Direct PDO connection to ensure data retrieval
            $pdo = $this->getPDOConnection();
            
            $stmt = $pdo->query("SELECT * FROM shift_types ORDER BY name");
            $shiftTypes = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            Log::info('ShiftTypesController - Retrieved shift types: ' . count($shiftTypes));
            
            return response()->json([
                'success' => true,
                'data' => $shiftTypes,
                'count' => count($shiftTypes)
            ]);
            
        } catch (\Exception $e) {
            Log::error('ShiftTypesController error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'count' => 0
            ]);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $pdo = $this->getPDOConnection();
            
            $stmt = $pdo->prepare("INSERT INTO shift_types (name, description, default_start_time, default_end_time, color_code, type, break_duration, hourly_rate, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            $result = $stmt->execute([
                $request->name,
                $request->description ?? '',
                $request->default_start_time,
                $request->default_end_time,
                $request->color_code ?? '#007bff',
                $request->type ?? 'regular',
                $request->break_duration ?? 30,
                $request->hourly_rate ?? 0,
                1
            ]);
            
            if ($result) {
                return redirect()->back()->with('success', 'Shift type created successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to create shift type.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error creating shift type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $pdo = $this->getPDOConnection();
            
            $stmt = $pdo->prepare("UPDATE shift_types SET name = ?, description = ?, default_start_time = ?, default_end_time = ?, color_code = ?, type = ?, break_duration = ?, hourly_rate = ?, updated_at = NOW() WHERE id = ?");
            
            $result = $stmt->execute([
                $request->name,
                $request->description ?? '',
                $request->default_start_time,
                $request->default_end_time,
                $request->color_code ?? '#007bff',
                $request->type ?? 'regular',
                $request->break_duration ?? 30,
                $request->hourly_rate ?? 0,
                $id
            ]);
            
            if ($result) {
                return redirect()->back()->with('success', 'Shift type updated successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to update shift type.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error updating shift type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        try {
            $pdo = $this->getPDOConnection();
            
            $stmt = $pdo->prepare("DELETE FROM shift_types WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return redirect()->back()->with('success', 'Shift type deleted successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to delete shift type.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error deleting shift type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
