<?php

namespace App\Services\NotificationService;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface NotificationService
{
    /**
     * Get paginated notifications for a specific employee (including broadcasts).
     */
    public function getPaginatedForEmployee(int $employeeId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get latest notifications for a specific employee (including broadcasts) with unread count.
     *
     * @return array{notifications: Collection, unread_count: int}
     */
    public function getLatestForEmployee(int $employeeId, int $limit = 10): array;

    /**
     * Mark a single notification as read for an employee.
     */
    public function markAsRead(int $notificationId, int $employeeId): void;

    /**
     * Mark all unread notifications as read for an employee.
     */
    public function markAllAsRead(int $employeeId): void;

    /**
     * Delete a notification by ID.
     */
    public function delete(int $id): void;
}
