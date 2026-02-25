<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationCategory;
use App\Models\Employment;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to an employee.
     * 
     * @param int $employeeId The ID of the employee to receive the notification
     * @param string $categoryName The name of the notification category (e.g., 'approval')
     * @param string $title The title of the notification
     * @param string $details The detailed message of the notification
     * @return void
     */
    public function send(int $employeeId, string $categoryName, string $title, string $details): void
    {
        try {
            $category = NotificationCategory::firstOrCreate(['name' => $categoryName]);

            Notification::create([
                'employee_id' => $employeeId,
                'category_id' => $category->id,
                'title' => $title,
                'details' => $details,
            ]);
            
            Log::info('Notification sent successfully', [
                'employee_id' => $employeeId,
                'category' => $categoryName,
                'title' => $title,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage(), [
                'employee_id' => $employeeId,
                'category' => $categoryName,
                'title' => $title,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send notification to an employment (using employment_id).
     * 
     * @param int $employmentId The ID of the employment
     * @param string $categoryName The name of the notification category
     * @param string $title The title of the notification
     * @param string $details The detailed message
     * @return void
     */
    public function sendToEmployment(int $employmentId, string $categoryName, string $title, string $details): void
    {
        $employment = Employment::find($employmentId);
        if ($employment && $employment->employee_id) {
            $this->send($employment->employee_id, $categoryName, $title, $details);
        } else {
            Log::warning('Could not send notification to employment: employment not found or employee_id missing', [
                'employment_id' => $employmentId
            ]);
        }
    }
}
