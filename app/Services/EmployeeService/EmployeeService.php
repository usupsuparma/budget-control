<?php

namespace App\Services\EmployeeService;

use App\Models\Employee;

interface EmployeeService
{
    /**
     * Resolve the uppline (direct manager) employee for a given job position.
     *
     * Logic (based on org hierarchy from EMPLOYEE_ORG_RESOLUTION.md):
     *   L1 Director  → no uppline (returns null)
     *   L2 Division  → structure_id = division.id → find the L1 employee in that division's director
     *   L3 Department → structure_id = department.id → find the L2 employee in that department's division
     *   L4+ Section/Staff → structure_id = section.id → find the L3 employee in that section's department
     *
     * @param  int  $jobPositionId
     * @return Employee|null  The employee holding the parent-level job position, or null if none found.
     */
    public function resolveUpplineForJobPosition(int $jobPositionId): ?Employee;

    /**
     * Create a new employee with their employment record.
     * Automatically resolves uppline from the job position hierarchy.
     *
     * @param  array  $data  Validated data from StoreEmployeeRequest
     * @return Employee
     */
    public function createEmployee(array $data): Employee;

    /**
     * Update an existing employee and their employment record.
     * Automatically resolves uppline from the job position hierarchy.
     *
     * @param  int    $id
     * @param  array  $data  Validated data from UpdateEmployeeRequest
     * @return Employee
     */
    public function updateEmployee(int $id, array $data): Employee;
}
