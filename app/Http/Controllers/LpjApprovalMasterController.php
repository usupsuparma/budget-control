<?php

namespace App\Http\Controllers;

use App\Models\LpjApprovalMaster;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LpjApprovalMasterController extends Controller
{
    /**
     * Display the LPJ Approval Master management page.
     */
    public function index()
    {
        $approvers = LpjApprovalMaster::with('employment.employee')
            ->ordered()
            ->get();

        $employments = Employment::with('employee')
            ->whereNotIn('id', $approvers->pluck('employment_id'))
            ->get()
            ->map(function ($employment) {
                return [
                    'id' => $employment->id,
                    'name' => $employment->employee 
                        ? $employment->employee->first_name . ' ' . $employment->employee->last_name 
                        : 'Unknown',
                    'job_position' => $employment->job_position_name,
                ];
            });

        return view('pages.master.lpj-approver', compact('approvers', 'employments'));
    }

    /**
     * Get all LPJ approvers data (AJAX).
     */
    public function getData()
    {
        try {
            $approvers = LpjApprovalMaster::with('employment.employee')
                ->ordered()
                ->get()
                ->map(function ($approver) {
                    $employee = $approver->employment->employee ?? null;
                    return [
                        'id' => $approver->id,
                        'employment_id' => $approver->employment_id,
                        'employee_name' => $employee 
                            ? $employee->first_name . ' ' . $employee->last_name 
                            : 'Unknown',
                        'job_position' => $approver->employment->job_position_name ?? '-',
                        'approval_sequence' => $approver->approval_sequence,
                        'is_active' => $approver->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $approvers
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting LPJ approvers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading data'
            ], 500);
        }
    }

    /**
     * Store a new LPJ approver.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employment_id' => 'required|exists:employment,id',
            'approval_sequence' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if employment already exists
            $existing = LpjApprovalMaster::where('employment_id', $request->employment_id)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'This employee is already an LPJ approver'
                ], 422);
            }

            $approver = LpjApprovalMaster::create([
                'employment_id' => $request->employment_id,
                'approval_sequence' => $request->approval_sequence,
                'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'LPJ Approver added successfully',
                'data' => $approver
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding LPJ approver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding approver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing LPJ approver.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approval_sequence' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $approver = LpjApprovalMaster::findOrFail($id);
            $approver->update([
                'approval_sequence' => $request->approval_sequence,
                'is_active' => filter_var($request->input('is_active', $approver->is_active), FILTER_VALIDATE_BOOLEAN),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'LPJ Approver updated successfully',
                'data' => $approver
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating LPJ approver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating approver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status of an LPJ approver.
     */
    public function toggleActive($id)
    {
        try {
            $approver = LpjApprovalMaster::findOrFail($id);
            $approver->update([
                'is_active' => !$approver->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'is_active' => $approver->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling LPJ approver status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    /**
     * Delete an LPJ approver.
     */
    public function destroy($id)
    {
        try {
            $approver = LpjApprovalMaster::findOrFail($id);
            $approver->delete();

            return response()->json([
                'success' => true,
                'message' => 'LPJ Approver removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting LPJ approver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing approver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available employees (not yet approvers).
     */
    public function getAvailableEmployees()
    {
        try {
            $existingIds = LpjApprovalMaster::pluck('employment_id');
            
            $employments = Employment::with('employee')
                ->whereNotIn('id', $existingIds)
                ->get()
                ->map(function ($employment) {
                    $employee = $employment->employee;
                    return [
                        'id' => $employment->id,
                        'name' => $employee 
                            ? $employee->first_name . ' ' . $employee->last_name 
                            : 'Unknown',
                        'job_position' => $employment->job_position_name ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employments
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available employees: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading employees'
            ], 500);
        }
    }
}
