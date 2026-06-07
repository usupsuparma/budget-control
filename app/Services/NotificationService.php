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
    public function send(
        int $employeeId,
        string $categoryName,
        string $title,
        string $details,
        ?string $referenceType = null,
        int|string|null $referenceId = null
    ): void
    {
        try {
            $category = NotificationCategory::firstOrCreate(['name' => $categoryName]);

            Notification::create([
                'employee_id' => $employeeId,
                'category_id' => $category->id,
                'title' => $title,
                'details' => $details,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId !== null ? (int) $referenceId : null,
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
    public function sendToEmployment(
        int $employmentId,
        string $categoryName,
        string $title,
        string $details,
        ?string $referenceType = null,
        int|string|null $referenceId = null
    ): void
    {
        $employment = Employment::find($employmentId);
        if ($employment && $employment->employee_id) {
            $this->send($employment->employee_id, $categoryName, $title, $details, $referenceType, $referenceId);
        } else {
            Log::warning('Could not send notification to employment: employment not found or employee_id missing', [
                'employment_id' => $employmentId
            ]);
        }
    }

    /**
     * Delete notifications tied to a workflow reference.
     */
    public function deleteByReference(
        string $categoryName,
        string $referenceType,
        int|string $referenceId,
        ?array $employeeIds = null
    ): int {
        try {
            $category = NotificationCategory::where('name', $categoryName)->first();

            if (! $category) {
                return 0;
            }

            $query = Notification::where('category_id', $category->id)
                ->where('reference_type', $referenceType)
                ->where('reference_id', (int) $referenceId);

            if ($employeeIds !== null) {
                $employeeIds = $this->normalizeEmployeeIds($employeeIds);

                if (empty($employeeIds)) {
                    return 0;
                }

                $query->whereIn('employee_id', $employeeIds);
            }

            $deleted = $query->delete();

            Log::info('Referenced notifications deleted', [
                'category' => $categoryName,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'employee_ids' => $employeeIds,
                'deleted_count' => $deleted,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete referenced notifications: ' . $e->getMessage(), [
                'category' => $categoryName,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'employee_ids' => $employeeIds,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete legacy notifications that do not have reference columns populated.
     */
    public function deleteMatching(
        string $categoryName,
        string $title,
        array $details,
        ?array $employeeIds = null
    ): int {
        try {
            $category = NotificationCategory::where('name', $categoryName)->first();

            if (! $category) {
                return 0;
            }

            $details = array_values(array_filter($details, fn ($detail) => $detail !== null && $detail !== ''));

            if (empty($details)) {
                return 0;
            }

            $query = Notification::where('category_id', $category->id)
                ->where('title', $title)
                ->whereIn('details', $details);

            if ($employeeIds !== null) {
                $employeeIds = $this->normalizeEmployeeIds($employeeIds);

                if (empty($employeeIds)) {
                    return 0;
                }

                $query->whereIn('employee_id', $employeeIds);
            }

            $deleted = $query->delete();

            Log::info('Matching notifications deleted', [
                'category' => $categoryName,
                'title' => $title,
                'details' => $details,
                'employee_ids' => $employeeIds,
                'deleted_count' => $deleted,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete matching notifications: ' . $e->getMessage(), [
                'category' => $categoryName,
                'title' => $title,
                'details' => $details,
                'employee_ids' => $employeeIds,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete task notifications with the same title/reference that are no longer actionable.
     */
    public function deleteTaskNotificationsExceptReferences(
        string $categoryName,
        string $title,
        string $referenceType,
        array $activeReferenceIds,
        ?array $employeeIds = null
    ): int {
        try {
            $category = NotificationCategory::where('name', $categoryName)->first();

            if (! $category) {
                return 0;
            }

            $activeReferenceIds = array_values(array_unique(array_map(
                fn ($referenceId) => (int) $referenceId,
                array_filter($activeReferenceIds, fn ($referenceId) => $referenceId !== null && $referenceId !== '')
            )));

            $query = Notification::where('category_id', $category->id)
                ->where('title', $title)
                ->where(function ($query) use ($referenceType) {
                    $query->where('reference_type', $referenceType)
                        ->orWhereNull('reference_type');
                });

            if (! empty($activeReferenceIds)) {
                $query->where(function ($query) use ($activeReferenceIds) {
                    $query->whereNull('reference_id')
                        ->orWhereNotIn('reference_id', $activeReferenceIds);
                });
            }

            if ($employeeIds !== null) {
                $employeeIds = $this->normalizeEmployeeIds($employeeIds);

                if (empty($employeeIds)) {
                    return 0;
                }

                $query->whereIn('employee_id', $employeeIds);
            }

            $deleted = $query->delete();

            Log::info('Stale task notifications deleted', [
                'category' => $categoryName,
                'title' => $title,
                'reference_type' => $referenceType,
                'active_reference_ids' => $activeReferenceIds,
                'employee_ids' => $employeeIds,
                'deleted_count' => $deleted,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete stale task notifications: ' . $e->getMessage(), [
                'category' => $categoryName,
                'title' => $title,
                'reference_type' => $referenceType,
                'active_reference_ids' => $activeReferenceIds,
                'employee_ids' => $employeeIds,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function normalizeEmployeeIds(array $employeeIds): array
    {
        return array_values(array_unique(array_map(
            fn ($employeeId) => (int) $employeeId,
            array_filter($employeeIds, fn ($employeeId) => $employeeId !== null && $employeeId !== '')
        )));
    }
}
