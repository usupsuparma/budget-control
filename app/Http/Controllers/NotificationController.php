<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function monitoring()
    {
        return view('notifications.monitoring.index');
    }

    public function index()
    {
        $employeeId = auth()->id();
        $notifications = Notification::with('category')
            ->where(function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereNull('employee_id');
            })
            ->latest()
            ->paginate(15);
            
        // Check read status for each notification
        foreach ($notifications as $notification) {
            $notification->is_read = $notification->reads()
                ->where('employee_id', $employeeId)
                ->where('is_read', true)
                ->exists();
        }

        return view('notifications.index', compact('notifications', 'employeeId'));
    }

    public function data()
    {
        $notifications = Notification::with(['category', 'employee'])->select('notifications.*');

        return DataTables::of($notifications)
            ->editColumn('category_id', function ($row) {
                return $row->category?->name ?? 'N/A';
            })
            ->editColumn('employee_id', function ($row) {
                return $row->employee?->name ?? 'All Employees';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-danger delete-notification" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function destroy($id)
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['success' => 'Notification deleted successfully.']);
    }

    public function getUserNotifications()
    {
        $employeeId = auth()->id();
        
        // Get notifications for this employee OR for all employees (employee_id is null)
        $notifications = Notification::with('category')
            ->where(function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereNull('employee_id');
            })
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = Notification::where(function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereNull('employee_id');
            })
            ->whereDoesntHave('reads', function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)->where('is_read', true);
            })
            ->count();

        $html = view('notifications.partials.list', compact('notifications', 'employeeId'))->render();

        return response()->json([
            'html' => $html,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead($id)
    {
        \App\Models\NotificationRead::updateOrCreate(
            ['notification_id' => $id, 'employee_id' => auth()->id()],
            ['is_read' => true]
        );

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $employeeId = auth()->id();
        $notifications = Notification::where(function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereNull('employee_id');
            })
            ->whereDoesntHave('reads', function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)->where('is_read', true);
            })
            ->get();

        foreach ($notifications as $notification) {
            \App\Models\NotificationRead::updateOrCreate(
                ['notification_id' => $notification->id, 'employee_id' => $employeeId],
                ['is_read' => true]
            );
        }

        return response()->json(['success' => true]);
    }
}
