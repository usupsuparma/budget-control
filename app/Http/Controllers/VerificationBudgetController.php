<?php

namespace App\Http\Controllers;

use App\Services\VerificationBudgetService\VerificationBudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificationBudgetController extends Controller
{
    protected VerificationBudgetService $verificationService;

    public function __construct(VerificationBudgetService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Display verification dashboard for verifiers
     */
    public function index()
    {
        return view('pages.budget.verification-budget');
    }

    /**
     * Submit budget item for verification (called from budget-user)
     */
    public function submitForVerification(int $itemId)
    {
        $result = $this->verificationService->submitForVerification($itemId);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Verify budget item (approve verification and set fix price)
     */
    public function verify(Request $request, int $itemId)
    {
        $request->validate([
            'fix_price' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $result = $this->verificationService->verifyBudget(
            $itemId,
            (float) $request->input('fix_price'),
            $request->input('notes')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Reject verification
     */
    public function reject(Request $request, int $itemId)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $result = $this->verificationService->rejectVerification(
            $itemId,
            $request->input('notes')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get items pending verification for current user
     */
    public function myPendingVerifications()
    {
        $result = $this->verificationService->getMyPendingVerifications();
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get verification status for an item
     */
    public function getStatus(int $itemId)
    {
        $result = $this->verificationService->getVerificationStatus($itemId);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Check if current user can verify an item
     */
    public function canVerify(int $itemId)
    {
        $canVerify = $this->verificationService->canVerify($itemId);
        return response()->json([
            'success' => true,
            'can_verify' => $canVerify,
        ]);
    }
}
