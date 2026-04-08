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
     * Note: Verification is now integrated in budget-user page as Tab 2
     * This route can redirect to budget-user with verification tab active
     */
    public function index()
    {
        // Redirect to budget-user page - verification is now a tab
        return redirect()->route('budget-user.index', ['tab' => 'verification']);
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
     * Bulk verify budget items
     */
    public function bulkVerify(Request $request)
    {
        try {
            $request->validate([
                'item_ids' => 'required|array',
                'item_ids.*' => 'exists:workplan_budget_items,id',
                'fix_prices' => 'nullable|array',
                'notes' => 'nullable|string|max:1000',
            ]);

            $result = $this->verificationService->bulkVerify(
                $request->input('item_ids'),
                $request->input('fix_prices', []),
                $request->input('notes')
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk verifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk reject verification
     */
    public function bulkReject(Request $request)
    {
        try {
            $request->validate([
                'item_ids' => 'required|array',
                'item_ids.*' => 'exists:workplan_budget_items,id',
                'notes' => 'required|string|max:1000',
            ]);

            $result = $this->verificationService->bulkReject(
                $request->input('item_ids'),
                $request->input('notes')
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk reject: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import CSV for verification
     */
    public function importCsv(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            $result = $this->verificationService->processCsvImport($request->file('file'));

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file CSV: ' . $e->getMessage(),
            ], 500);
        }
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
