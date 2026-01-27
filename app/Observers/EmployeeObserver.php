<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Employment;

class EmployeeObserver
{
    /**
     * Handle the Employee "creating" event.
     * Generate employee_code (NIP) sebelum disimpan ke database
     */
    public function creating(Employee $employee): void
    {
        if (empty($employee->employee_code)) {
            $employee->employee_code = $this->generateEmployeeCode();
        }
    }

    /**
     * Handle the Employee "created" event.
     * Buat record Employment dengan employee_id = employee.id (FK)
     */
    public function created(Employee $employee): void
    {
        // Buat record Employment otomatis jika belum ada
        // employment.employee_id references employee.id (integer FK)
        if (!$employee->employment) {
            Employment::create([
                'employee_id' => $employee->id, // FK ke employee.id
                'status' => 'active',
            ]);
        }
    }

    /**
     * Generate employee_code (NIP) unik dengan format EMP-YYYYMMDD-XXXX
     */
    private function generateEmployeeCode(): string
    {
        $date = date('Ymd');
        $prefix = 'EMP-' . $date . '-';
        
        // Cari employee_code terakhir dengan prefix yang sama
        $lastEmployee = Employee::where('employee_code', 'like', $prefix . '%')
            ->orderBy('employee_code', 'desc')
            ->first();
        
        if ($lastEmployee) {
            // Ambil nomor urut terakhir dan tambah 1
            $lastNumber = (int) substr($lastEmployee->employee_code, -4);
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
        // Employment FK sekarang menggunakan employee.id yang tidak berubah
        // Tidak perlu sync employee_code ke employment karena FK pakai id
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
        // Employment sekarang terhubung via employee_id = employee.id
        Employment::withTrashed()
            ->where('employee_id', $employee->id)
            ->restore();
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        // Force delete employment jika ada
        Employment::withTrashed()
            ->where('employee_id', $employee->id)
            ->forceDelete();
    }
}
