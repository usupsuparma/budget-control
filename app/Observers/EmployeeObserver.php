<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Employment;

class EmployeeObserver
{
    /**
     * Handle the Employee "creating" event.
     * Generate employee_id sebelum disimpan ke database
     */
    public function creating(Employee $employee): void
    {
        if (empty($employee->employee_id)) {
            $employee->employee_id = $this->generateEmployeeId();
        }
    }

    /**
     * Handle the Employee "created" event.
     * Buat record Employment dengan employee_id yang sama
     */
    public function created(Employee $employee): void
    {
        // Buat record Employment otomatis jika belum ada
        if (!$employee->employment) {
            Employment::create([
                'employee_id' => $employee->employee_id,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Generate employee_id unik dengan format EMP-YYYYMMDD-XXXX
     */
    private function generateEmployeeId(): string
    {
        $date = date('Ymd');
        $prefix = 'EMP-' . $date . '-';
        
        // Cari employee_id terakhir dengan prefix yang sama
        $lastEmployee = Employee::where('employee_id', 'like', $prefix . '%')
            ->orderBy('employee_id', 'desc')
            ->first();
        
        if ($lastEmployee) {
            // Ambil nomor urut terakhir dan tambah 1
            $lastNumber = (int) substr($lastEmployee->employee_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // Format dengan 4 digit
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        // Sync employee_id ke employment jika berubah
        if ($employee->isDirty('employee_id') && $employee->employment) {
            $employee->employment->update([
                'employee_id' => $employee->employee_id
            ]);
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        // Soft delete employment jika ada (cascade akan handle ini dari database)
        // Tidak perlu action manual karena sudah ada foreign key cascade
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        // Restore employment jika ada dan di soft delete
        Employment::withTrashed()
            ->where('employee_id', $employee->employee_id)
            ->restore();
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        // Force delete employment jika ada
        Employment::withTrashed()
            ->where('employee_id', $employee->employee_id)
            ->forceDelete();
    }
}
