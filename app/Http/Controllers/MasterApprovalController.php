<?php

namespace App\Http\Controllers;

use App\Models\ApprovalFlowDetail;
use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalModule;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MasterApprovalController extends Controller
{
    public function __construct() {}

    /**
     * Display approval dashboard
     */
    public function index()
    {
        $title = 'Approval Management';
        $user = Auth::user();

        return view('pages.approval.main', compact('title'));
    }

    // ========== NEW: Approval Modules Management ==========

    /**
     * Get all approval modules
     */
    public function getModules()
    {
        try {
            $modules = ApprovalModule::orderBy('module_name')->get();

            return response()->json([
                'success' => true,
                'data' => $modules,
            ]);
        } catch (\Exception $e) {
            Log::error('Get modules failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get modules',
            ], 500);
        }
    }

    /**
     * Get available tables for new module creation
     */
    public function getAvailableTables(Request $request)
    {
        try {
            $excludeId = $request->input('exclude_id');

            if ($excludeId) {
                $tables = ApprovalModule::getAvailableTablesForEdit((int) $excludeId);
            } else {
                $tables = ApprovalModule::getAvailableTables();
            }

            return response()->json([
                'success' => true,
                'data' => $tables,
            ]);
        } catch (\Exception $e) {
            Log::error('Get available tables failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get available tables',
            ], 500);
        }
    }

    /**
     * Get modules for dropdown (filter out modules already used in templates)
     */
    public function getModulesForDropdown(Request $request)
    {
        try {
            $excludeTemplateId = $request->input('exclude_template_id');

            // Get all active modules
            $allModules = ApprovalModule::where('is_active', true)
                ->orderBy('module_name')
                ->get();

            // Get module IDs that are already used in templates
            $usedModuleIds = ApprovalFlowTemplate::when($excludeTemplateId, function ($query) use ($excludeTemplateId) {
                return $query->where('id', '!=', $excludeTemplateId);
            })
                ->pluck('module_id')
                ->toArray();

            // Filter out used modules
            $availableModules = $allModules->filter(function ($module) use ($usedModuleIds) {
                return ! in_array($module->id, $usedModuleIds);
            })->values();

            return response()->json([
                'success' => true,
                'data' => $availableModules,
            ]);
        } catch (\Exception $e) {
            Log::error('Get modules for dropdown failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get modules for dropdown',
            ], 500);
        }
    }

    /**
     * Store new approval module
     */
    public function storeModule(Request $request)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $allowedTables = array_keys(ApprovalModule::ALLOWED_TABLES);

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|max:50|unique:approval_modules,module_name',
            'table_name' => 'required|string|in:'.implode(',', $allowedTables).'|unique:approval_modules,table_name',
            'is_active' => 'boolean',
        ], [
            'table_name.in' => 'Table yang dipilih tidak valid.',
            'table_name.unique' => 'Module untuk table ini sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $module = ApprovalModule::create([
                'module_name' => $request->input('module_name'),
                'table_name' => $request->input('table_name'),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Module created successfully',
                'data' => $module,
            ]);
        } catch (\Exception $e) {
            Log::error('Store module failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create module',
            ], 500);
        }
    }

    /**
     * Update approval module
     */
    public function updateModule(Request $request, $id)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $allowedTables = array_keys(ApprovalModule::ALLOWED_TABLES);

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|max:50|unique:approval_modules,module_name,'.$id,
            'table_name' => 'required|string|in:'.implode(',', $allowedTables).'|unique:approval_modules,table_name,'.$id,
            'is_active' => 'boolean',
        ], [
            'table_name.in' => 'Table yang dipilih tidak valid.',
            'table_name.unique' => 'Module untuk table ini sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $module = ApprovalModule::findOrFail($id);
            $module->update([
                'module_name' => $request->input('module_name'),
                'table_name' => $request->input('table_name'),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully',
                'data' => $module,
            ]);
        } catch (\Exception $e) {
            Log::error('Update module failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update module',
            ], 500);
        }
    }

    /**
     * Delete approval module
     */
    public function deleteModule($id)
    {
        try {
            $module = ApprovalModule::findOrFail($id);

            // Cek apakah modul memiliki template yang terkait
            if ($module->templates()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete module. It has associated templates.',
                ], 400);
            }

            $module->delete();

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete module failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module',
            ], 500);
        }
    }

    // ========== NEW: Flow Templates Management ==========

    /**
     * Get all flow templates with module relation
     */
    public function getTemplates()
    {
        try {
            $templates = ApprovalFlowTemplate::with('module')
                ->orderBy('priority')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates,
            ]);
        } catch (\Exception $e) {
            Log::error('Get templates failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get templates',
            ], 500);
        }
    }

    /**
     * Get all templates with their flow details for direct list view
     */
    public function getAllTemplatesWithFlowDetails()
    {
        try {
            $templates = ApprovalFlowTemplate::with([
                'module',
                'details.employment.employee',
            ])
                ->where('is_active', true)
                ->orderBy('priority')
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'template_name' => $template->template_name,
                        'module_name' => $template->module->module_name ?? '-',
                        'priority' => $template->priority,
                        'use_uppline_chain' => $template->use_uppline_chain,
                        'use_threshold' => $template->use_threshold,
                        'approvers_count' => $template->details->count(),
                        'approvers' => $template->details->map(function ($detail) {
                            $employeeName = 'N/A';
                            if ($detail->employment && $detail->employment->employee) {
                                $firstName = $detail->employment->employee->first_name ?? '';
                                $lastName = $detail->employment->employee->last_name ?? '';
                                $employeeName = trim($firstName.' '.$lastName);
                            }

                            return [
                                'id' => $detail->id,
                                'level_sequence' => $detail->level_sequence,
                                'employment_id' => $detail->employment_id,
                                'employee_name' => $employeeName,
                                'threshold_amount' => $detail->threshold_amount,
                                'is_required' => $detail->is_required,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $templates,
            ]);
        } catch (\Exception $e) {
            Log::error('Get templates with flow details failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get templates with flow details',
            ], 500);
        }
    }

    /**
     * Store new flow template
     */
    public function storeTemplate(Request $request)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'use_uppline_chain' => filter_var($request->input('use_uppline_chain', false), FILTER_VALIDATE_BOOLEAN),
            'use_threshold' => filter_var($request->input('use_threshold', false), FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:approval_modules,id|unique:approval_flow_templates,module_id',
            'template_name' => 'required|string|max:100',
            'use_uppline_chain' => 'boolean',
            'use_threshold' => 'boolean',
            'condition_field' => 'nullable|string|max:50',
            'priority' => 'integer|min:1',
            'is_active' => 'boolean',
        ], [
            'module_id.required' => 'Module harus dipilih.',
            'module_id.exists' => 'Module tidak valid.',
            'module_id.unique' => 'Module sudah memiliki template approval. Setiap module hanya boleh memiliki satu template.',
            'template_name.required' => 'Template name harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get condition_field from selected module
            $module = ApprovalModule::findOrFail($request->input('module_id'));

            $template = ApprovalFlowTemplate::create([
                'module_id' => $request->input('module_id'),
                'template_name' => $request->input('template_name'),
                'use_uppline_chain' => $request->input('use_uppline_chain', false),
                'use_threshold' => $request->input('use_threshold', false),
                'condition_field' => $module->condition_field,
                'priority' => $request->input('priority', 1),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template->load('module'),
            ]);
        } catch (\Exception $e) {
            Log::error('Store template failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
            ], 500);
        }
    }

    /**
     * Update flow template
     */
    public function updateTemplate(Request $request, $id)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'use_uppline_chain' => filter_var($request->input('use_uppline_chain', false), FILTER_VALIDATE_BOOLEAN),
            'use_threshold' => filter_var($request->input('use_threshold', false), FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        // NOTE: module_id is NOT included - cannot be changed on update
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:100',
            'use_uppline_chain' => 'boolean',
            'use_threshold' => 'boolean',
            'priority' => 'integer|min:1',
            'is_active' => 'boolean',
        ], [
            'template_name.required' => 'Template name harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = ApprovalFlowTemplate::findOrFail($id);

            // Get condition_field from existing module (module cannot be changed)
            $module = ApprovalModule::findOrFail($template->module_id);

            // Update template - module_id is NOT updated
            $template->update([
                'template_name' => $request->input('template_name'),
                'use_uppline_chain' => $request->input('use_uppline_chain', false),
                'use_threshold' => $request->input('use_threshold', false),
                'condition_field' => $module->condition_field,
                'priority' => $request->input('priority', 1),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $template->load('module'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update template failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
            ], 500);
        }
    }

    /**
     * Delete flow template
     */
    public function deleteTemplate($id)
    {
        try {
            $template = ApprovalFlowTemplate::findOrFail($id);

            // Cek apakah template memiliki details yang terkait
            if ($template->details()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete template. It has associated flow details.',
                ], 400);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete template failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
            ], 500);
        }
    }

    // ========== NEW: Flow Details Management ==========

    /**
     * Get flow details for a specific template
     */
    public function getFlowDetails($templateId)
    {
        try {
            $details = ApprovalFlowDetail::with(['template', 'employment.employee'])
                ->where('template_id', $templateId)
                ->orderBy('level_sequence')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error('Get flow details failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get flow details',
            ], 500);
        }
    }

    /**
     * Store new flow detail
     */
    public function storeFlowDetail(Request $request)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_required' => filter_var($request->input('is_required', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:approval_flow_templates,id',
            'level_sequence' => 'required|integer|min:1',
            'employment_id' => 'required|exists:employment,id',
            'threshold_amount' => 'nullable|numeric|min:0',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $detail = ApprovalFlowDetail::create([
                'template_id' => $request->input('template_id'),
                'level_sequence' => $request->input('level_sequence'),
                'employment_id' => $request->input('employment_id'),
                'threshold_amount' => $request->input('threshold_amount'),
                'is_required' => $request->input('is_required', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flow detail created successfully',
                'data' => $detail->load(['template', 'employment.employee']),
            ]);
        } catch (\Exception $e) {
            Log::error('Store flow detail failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create flow detail',
            ], 500);
        }
    }

    /**
     * Update flow detail
     */
    public function updateFlowDetail(Request $request, $id)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_required' => filter_var($request->input('is_required', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:approval_flow_templates,id',
            'level_sequence' => 'required|integer|min:1',
            'employment_id' => 'required|exists:employment,id',
            'threshold_amount' => 'nullable|numeric|min:0',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $detail = ApprovalFlowDetail::findOrFail($id);
            $employment = Employment::findOrFail($request->input('employment_id'));
            $name = $employment->employee->name;
            $detail->update([
                'template_id' => $request->input('template_id'),
                'level_sequence' => $request->input('level_sequence'),
                'employment_id' => $request->input('employment_id'),
                'threshold_amount' => $request->input('threshold_amount'),
                'is_required' => $request->input('is_required', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flow detail updated successfully',
                'data' => $detail->load(['template', 'employment.employee']),
            ]);
        } catch (\Exception $e) {
            Log::error('Update flow detail failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update flow detail',
            ], 500);
        }
    }

    /**
     * Delete flow detail
     */
    public function deleteFlowDetail($id)
    {
        try {
            $detail = ApprovalFlowDetail::findOrFail($id);
            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Flow detail deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete flow detail failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete flow detail',
            ], 500);
        }
    }

    // ========== NEW: Helper - Get Employments ==========

    /**
     * Get all employments for dropdown
     */
    public function getEmployments()
    {
        try {
            // Debug: Log the total count first
            $totalEmployments = Employment::count();
            $employmentsWithEmployee = Employment::whereHas('employee')->count();
            Log::info("Employment Stats - Total: {$totalEmployments}, With Employee: {$employmentsWithEmployee}");

            $employments = Employment::with('employee')
                ->whereHas('employee')
                ->get()
                ->map(function ($employment) {
                    $employee = $employment->employee;
                    $employeeName = 'N/A';

                    if ($employee) {
                        // Try using name accessor first, fallback to manual concat
                        $employeeName = $employee->name ??
                            (($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                        $employeeName = trim($employeeName);

                        // Add job position if available
                        if (! empty($employment->job_position_name)) {
                            $employeeName .= ' ('.$employment->job_position_name.')';
                        }
                    }

                    return [
                        'id' => $employment->id,
                        'employee_name' => $employeeName ?: 'Unknown Employee',
                        'employee_id' => $employment->employee_id,
                        'job_position' => $employment->job_position_name ?? null,
                        'organization' => $employment->organization_name ?? null,
                    ];
                })
                ->sortBy('employee_name')
                ->values();

            Log::info('Returning '.count($employments).' employments');

            return response()->json([
                'success' => true,
                'data' => $employments,
            ]);
        } catch (\Exception $e) {
            Log::error('Get employments failed: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get employments: '.$e->getMessage(),
            ], 500);
        }
    }

    // ========== NEW: Uppline Configs Management ==========

    /**
     * Get uppline configs for a template
     */
    public function getUpplineConfigs($templateId)
    {
        try {
            $configs = \App\Models\ApprovalFlowUpplineConfigs::where('template_id', $templateId)
                ->orderBy('step_sequence', 'asc')
                ->get()
                ->map(function ($config) {
                    return [
                        'id' => $config->id,
                        'template_id' => $config->template_id,
                        'division_id' => $config->division_id,
                        'division_name' => $config->division_id
                            ? optional(\App\Models\Division::find($config->division_id))->name
                            : 'Default (All Division)',
                        'step_sequence' => $config->step_sequence,
                        'job_level_name' => $config->job_level_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $configs,
            ]);
        } catch (\Exception $e) {
            Log::error('Get uppline configs failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get uppline configs',
            ], 500);
        }
    }

    /**
     * Store new uppline config
     */
    public function storeUpplineConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:approval_flow_templates,id',
            'division_id' => 'nullable|exists:division,id',
            'step_sequence' => 'required|integer|min:1',
            'job_level_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check for duplicate step_sequence in same template+division
            $existing = \App\Models\ApprovalFlowUpplineConfigs::where('template_id', $request->template_id)
                ->where('step_sequence', $request->step_sequence)
                ->where(function ($query) use ($request) {
                    if ($request->division_id) {
                        $query->where('division_id', $request->division_id);
                    } else {
                        $query->whereNull('division_id');
                    }
                })
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step sequence already exists for this template and division',
                ], 422);
            }

            $config = \App\Models\ApprovalFlowUpplineConfigs::create([
                'template_id' => $request->template_id,
                'division_id' => $request->division_id,
                'step_sequence' => $request->step_sequence,
                'job_level_name' => $request->job_level_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Uppline config created successfully',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            Log::error('Store uppline config failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create uppline config',
            ], 500);
        }
    }

    /**
     * Update uppline config
     */
    public function updateUpplineConfig(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'nullable|exists:division,id',
            'step_sequence' => 'required|integer|min:1',
            'job_level_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $config = \App\Models\ApprovalFlowUpplineConfigs::findOrFail($id);

            // Check for duplicate step_sequence (excluding current record)
            $existing = \App\Models\ApprovalFlowUpplineConfigs::where('template_id', $config->template_id)
                ->where('step_sequence', $request->step_sequence)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($request) {
                    if ($request->division_id) {
                        $query->where('division_id', $request->division_id);
                    } else {
                        $query->whereNull('division_id');
                    }
                })
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step sequence already exists for this template and division',
                ], 422);
            }

            $config->update([
                'division_id' => $request->division_id,
                'step_sequence' => $request->step_sequence,
                'job_level_name' => $request->job_level_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Uppline config updated successfully',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            Log::error('Update uppline config failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update uppline config',
            ], 500);
        }
    }

    /**
     * Delete uppline config
     */
    public function deleteUpplineConfig($id)
    {
        try {
            $config = \App\Models\ApprovalFlowUpplineConfigs::findOrFail($id);
            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Uppline config deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete uppline config failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete uppline config',
            ], 500);
        }
    }

    /**
     * Get divisions for dropdown
     */
    public function getDivisions()
    {
        try {
            $divisions = \App\Models\Division::select('id', 'name as division_name')
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $divisions,
            ]);
        } catch (\Exception $e) {
            Log::error('Get divisions failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get divisions',
            ], 500);
        }
    }

    /**
     * Get job levels for dropdown
     */
    public function getJobLevels()
    {
        try {
            $jobLevels = \App\Models\JobLevel::select('id', 'job_level_name')
                ->where('status', 'active')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jobLevels,
            ]);
        } catch (\Exception $e) {
            Log::error('Get job levels failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get job levels',
            ], 500);
        }
    }
}
