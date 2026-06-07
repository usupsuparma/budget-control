<?php

namespace App\Http\Controllers;

use App\Services\BudgetResumeService\BudgetResumeService;
use Illuminate\Http\Request;

class BudgetResumeController extends Controller
{
    public function __construct(private readonly BudgetResumeService $budgetResumeService) {}

    public function index(Request $request)
    {
        return view('pages.budget.budget-resume', $this->budgetResumeService->getPageData($request->query()));
    }

    public function searchBudgetCodes(Request $request)
    {
        try {
            $query = $request->input('q') ?? '';
            $limit = min((int) $request->input('limit', 10), 100);
            $page = max(1, (int) $request->input('page', 1));

            return response()->json($this->budgetResumeService->searchBudgetCodes($query, $limit, $page));
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search budget codes',
                'data' => [],
                'has_more' => false,
                'page' => 1,
                'total' => 0,
            ], 500);
        }
    }

    public function getBudgetCodeByCode(Request $request)
    {
        try {
            return response()->json($this->budgetResumeService->getBudgetCodeByCode($request->input('code', '')));
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'data' => null,
            ], 500);
        }
    }
}
