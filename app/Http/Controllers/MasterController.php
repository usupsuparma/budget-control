<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Section;
use App\Services\MasterDataService\MasterDataService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class MasterController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}
    public function index()
    {
        $title = 'Master Data';
        return view('pages.settings.Settings', compact('title'));
    }

    public function index2()
    {
        $title = 'Master Data';

        return view('pages.settings.Settings', compact('title'));
    }

    public function data(Request $request)
    {
        // ... (existing data code)
    }

    public function options()
    {
        try {
            $options = $this->masterDataService->getAllOptions();
            return response()->json([
                'success' => true,
                'data' => $options
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch options'
            ], 500);
        }
    }

    public function organization()
    {
        $directors = $this->masterDataService->getOrganizationTree();
        return view('pages.settings.organization', compact('directors'))->render();
    }
}
