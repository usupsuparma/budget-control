<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Employment extends Model
{
    use SoftDeletes;

    protected $table = 'employment';

    protected $guarded = [];

    protected $dates = ['deleted_at', 'join_date'];

    protected $casts = [
        'join_date' => 'date',
    ];

    protected $fillable = [
        'employee_id', // FK to employee.id (integer, bukan NIP)
        'organization_id',
        'organization_name',
        'job_level_id',
        'job_level_name',
        'job_position_id',
        'job_position_name',
        'uppline_id',
        'uppline_id_name',
        'employment_status',
        'join_date',
        'status',
    ];

    /**
     * Get role name from associated Employee (via Spatie)
     */
    public function getRoleName(): string
    {
        return $this->employee?->roles->first()?->name ?? 'No Role';
    }

    /**
     * Get role attribute accessor
     */
    public function getRoleAttribute(): ?string
    {
        return $this->getRoleName();
    }

    /**
     * Get the employee that owns this employment.
     * employment.employee_id (FK) references employee.id (PK)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function jobLevel()
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id', 'id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'id');
    }

    public function uppline()
    {
        return $this->belongsTo(Employee::class, 'uppline_id', 'id');
    }

    /**
     * Get uppline's employment (for recursive uppline chain)
     * Note: uppline_id references Employee.id
     */
    public function upplineEmployment()
    {
        return $this->hasOneThrough(
            Employment::class,      // Final model
            Employee::class,        // Intermediate model
            'id',                   // FK on Employee (employee.id)
            'employee_id',          // FK on Employment (employment.employee_id -> employee.id)
            'uppline_id',           // Local key on this Employment (uppline_id points to Employee.id)
            'id'                    // Local key on Employee (employee.id)
        );
    }

    public function uplineEmployeesTopDown(array $levels = [1,2,3,4]): Collection
    {
        $employees = collect();

        $current = $this;

        while ($current && $current->uppline_id) { // uppline_id -> employee.id :contentReference[oaicite:3]{index=3}
            // Ambil employment milik uplinenya (1 step up)
            $uplineEmployment = $current->upplineEmployment()
                ->with('employee') // relasi employee() :contentReference[oaicite:4]{index=4}
                ->first();

            if (!$uplineEmployment || !$uplineEmployment->employee) {
                break;
            }

            $lvl = (int) $uplineEmployment->job_level_id; // :contentReference[oaicite:5]{index=5}

            if (in_array($lvl, $levels, true)) {
                // push Employee; sisipkan info level di object (opsional, biar enak dipakai)
                $emp = $uplineEmployment->employee;
                $emp->setAttribute('upline_job_level_id', $lvl);
                $employees->push($emp);
            }

            $current = $uplineEmployment; // lanjut naik
        }

        // Top-down: level 1 paling atas
        return $employees
            ->unique('id')
            ->sortBy(fn ($e) => (int) $e->upline_job_level_id)
            ->values();
    }
}
