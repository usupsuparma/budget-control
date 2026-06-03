<?php

namespace App\Services\EmployeeService;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobPosition;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class EmployeeServiceImpl implements EmployeeService
{
    /**
     * Resolve the uppline (direct manager) employee for a given job position.
     *
     * Hierarchy rules (EMPLOYEE_ORG_RESOLUTION.md):
     *   L1 Director        → structure_id = director.id  → no parent → returns null
     *   L2 Division Mgr    → structure_id = division.id  → parent = L1 employee in that division's director
     *   L3 Department Mgr  → structure_id = department.id → parent = L2 employee in that department's division
     *   L4+ Section/Staff  → structure_id = section.id  → parent = L3 employee in that section's department
     */
    public function resolveUpplineForJobPosition(int $jobPositionId): ?Employee
    {
        $jobPosition = JobPosition::find($jobPositionId);

        if (! $jobPosition || ! $jobPosition->structure_id) {
            return null;
        }

        $level       = (int) $jobPosition->job_level_id;
        $structureId = (int) $jobPosition->structure_id;

        // Find the parent structure_id and parent level based on current level
        $parentLevelId   = null;
        $parentStructId  = null;

        switch ($level) {
            case 1: // Director — no uppline
                return null;

            case 2: // Division Manager → uppline = L1 employee whose director owns this division
                $division = Division::find($structureId);
                if (! $division || ! $division->director_id) {
                    return null;
                }
                $parentLevelId  = 1;
                $parentStructId = (int) $division->director_id;
                break;

            case 3: // Department Manager → uppline = L2 employee whose division owns this department
                $department = Department::find($structureId);
                if (! $department || ! $department->division_id) {
                    return null;
                }
                $parentLevelId  = 2;
                $parentStructId = (int) $department->division_id;
                break;

            default: // L4+ Section / Staff → uppline = L3 employee whose department owns this section
                $section = Section::find($structureId);
                if (! $section || ! $section->department_id) {
                    return null;
                }
                $parentLevelId  = 3;
                $parentStructId = (int) $section->department_id;
                break;
        }

        // Find the job position(s) at the parent level that own the parent structure
        $parentJobPositionIds = JobPosition::where('job_level_id', $parentLevelId)
            ->where('structure_id', $parentStructId)
            ->where('status', 'Active')
            ->pluck('id');

        if ($parentJobPositionIds->isEmpty()) {
            return null;
        }

        // Find the employee currently holding one of those parent job positions
        $parentEmployment = Employment::whereIn('job_position_id', $parentJobPositionIds)
            ->where('status', 'Active')
            ->with('employee')
            ->first();

        if (! $parentEmployment || ! $parentEmployment->employee) {
            return null;
        }

        return $parentEmployment->employee;
    }

    /**
     * Create employee + employment atomically.
     * Uppline is auto-resolved from job position hierarchy.
     */
    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $jobPosition = JobPosition::findOrFail($data['job_position_id']);
            $role        = Role::where('id', $data['role_name'])->firstOrFail();

            $employee = Employee::create([
                'employee_code' => $data['employee_code'],
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'email'         => $data['email'],
                'password'      => bcrypt($data['password']),
                'status'        => 'Active',
            ]);

            $employee->assignRole($role->name);

            $uppline = $this->resolveUpplineForJobPosition($jobPosition->id);

            Employment::create([
                'employee_id'       => $employee->id,
                'job_level_id'      => $jobPosition->job_level_id,
                'job_level_name'    => $jobPosition->job_level_name,
                'job_position_id'   => $jobPosition->id,
                'job_position_name' => $jobPosition->job_position_name,
                'organization_id'   => $jobPosition->structure_id,
                'organization_name' => $jobPosition->structure_name,
                'uppline_id'        => $uppline?->id,
                'uppline_id_name'   => $uppline ? ($uppline->first_name . ' ' . $uppline->last_name) : null,
                'employment_status' => 'Aktif',
                'status'            => 'Active',
            ]);

            return $employee->load(['employment', 'roles']);
        });
    }

    /**
     * Update employee + employment atomically.
     * Uppline is auto-resolved from job position hierarchy.
     */
    public function updateEmployee(int $id, array $data): Employee
    {
        return DB::transaction(function () use ($id, $data) {
            $employee    = Employee::findOrFail($id);
            $jobPosition = JobPosition::findOrFail($data['job_position_id']);
            $role        = Role::where('id', $data['role_name'])->firstOrFail();

            $employee->update([
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'employee_code' => $data['employee_code'],
                'email'         => $data['email'],
                'status'        => $data['status'],
            ]);

            $employee->syncRoles([$role->name]);

            $uppline = $this->resolveUpplineForJobPosition($jobPosition->id);

            Employment::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'job_position_id'   => $jobPosition->id,
                    'job_position_name' => $jobPosition->job_position_name,
                    'organization_id'   => $jobPosition->structure_id,
                    'organization_name' => $jobPosition->structure_name,
                    'job_level_id'      => $jobPosition->job_level_id,
                    'job_level_name'    => $jobPosition->job_level_name,
                    'uppline_id'        => $uppline?->id,
                    'uppline_id_name'   => $uppline ? ($uppline->first_name . ' ' . $uppline->last_name) : null,
                    'employment_status' => $data['status'] === 'Active' ? 'Aktif' : 'Unaktif',
                    'status'            => $data['status'],
                ]
            );

            return $employee->load(['employment', 'roles']);
        });
    }
}
