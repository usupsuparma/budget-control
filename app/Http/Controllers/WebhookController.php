<?php

namespace App\Http\Controllers;

use App\Services\SubmissionService\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly SubmissionService $submissionService
    ) {}

    /**
     * Handle webhook to update transaction status to COMPLETED.
     * 
     * Expects: POST { "id": 123 }
     */
    public function updateTransactionCompleted(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required'
            ], 400);
        }

        try {
            $result = $this->submissionService->updateStatusToCompleted((int) $id);
            
            $statusCode = $result['success'] ? 200 : 422;
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Webhook updateTransactionCompleted error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
