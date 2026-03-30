<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $service) {}

    public function monitoring()
    {
        return view('notifications.monitoring.index');
    }

    public function index()
    {
        $employeeId    = auth()->id();
        $notifications = $this->service->getPaginatedForEmployee($employeeId);

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
        try {
            $this->service->delete((int) $id);
            return response()->json(['success' => true, 'message' => 'Notification deleted successfully.']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }

    public function getUserNotifications()
    {
        $employeeId = auth()->id();
        $result     = $this->service->getLatestForEmployee($employeeId);

        $notifications = $result['notifications'];
        $html          = view('notifications.partials.list', compact('notifications', 'employeeId'))->render();

        return response()->json([
            'html'         => $html,
            'unread_count' => $result['unread_count'],
        ]);
    }

    public function markAsRead($id)
    {
        try {
            $this->service->markAsRead((int) $id, auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $this->service->markAllAsRead(auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
        }
    }
}
