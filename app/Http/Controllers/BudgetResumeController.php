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
}
