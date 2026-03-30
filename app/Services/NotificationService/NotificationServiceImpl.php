<?php

namespace App\Services\NotificationService;

use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificationServiceImpl implements NotificationService
{
    public function getPaginatedForEmployee(int $employeeId, int $perPage = 15): LengthAwarePaginator
    {
        $notifications = Notification::with('category')
            ->where(function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                    ->orWhereNull('employee_id');
            })
            ->latest()
            ->paginate($perPage);

        foreach ($notifications as $notification) {
            $notification->is_read = $notification->reads()
                ->where('employee_id', $employeeId)
                ->where('is_read', true)
                ->exists();
        }

        return $notifications;
    }

    public function getLatestForEmployee(int $employeeId, int $limit = 10): array
    {
        $notifications = Notification::with('category')
            ->where(function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                    ->orWhereNull('employee_id');
            })
            ->latest()
            ->limit($limit)
            ->get();

        $unreadCount = Notification::where(function ($query) use ($employeeId) {
            $query->where('employee_id', $employeeId)
                ->orWhereNull('employee_id');
        })
            ->whereDoesntHave('reads', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)->where('is_read', true);
            })
            ->count();

        return [
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ];
    }

    public function markAsRead(int $notificationId, int $employeeId): void
    {
        NotificationRead::updateOrCreate(
            ['notification_id' => $notificationId, 'employee_id' => $employeeId],
            ['is_read' => true]
        );
    }

    public function markAllAsRead(int $employeeId): void
    {
        $notifications = Notification::where(function ($query) use ($employeeId) {
            $query->where('employee_id', $employeeId)
                ->orWhereNull('employee_id');
        })
            ->whereDoesntHave('reads', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)->where('is_read', true);
            })
            ->get();

        foreach ($notifications as $notification) {
            NotificationRead::updateOrCreate(
                ['notification_id' => $notification->id, 'employee_id' => $employeeId],
                ['is_read' => true]
            );
        }
    }

    public function delete(int $id): void
    {
        Notification::findOrFail($id)->delete();
    }
}
