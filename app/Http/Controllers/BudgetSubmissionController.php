<?php

namespace App\Http\Controllers;

use App\Services\BudgetSubmissionApprovalService\BudgetSubmissionApprovalService;
use App\Services\BudgetSubmissionService\BudgetSubmissionService;
use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;
use App\Exceptions\DomainException;
use App\Http\Requests\StoreBudgetSubmissionRequest;
use App\Http\Requests\UpdateBudgetSubmissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BudgetSubmissionController extends Controller
{
    public function __construct(
        private readonly BudgetSubmissionService $budgetSubmissionService,
        private readonly BudgetSubmissionApprovalService $budgetSubmissionApprovalService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $data = $this->budgetSubmissionService->getIndexData($user);

        return view('pages.budget.budget-submission', $data);
    }

    /**
     * Get table data via AJAX (for refreshing without page reload)
     */
    public function getData(Request $request)
    {
        try {
            $user = Auth::user();
            $budgetSubmissions = $this->budgetSubmissionService->getAjaxData($user);

            $html = '';
            $no = 1;

            foreach ($budgetSubmissions as $submission) {
                $statusLabel = $submission->approval_progress_label;
                $statusColor = $submission->status_color;
                if ($submission->isPending() && $submission->hasPendingApproval()) {
                    $statusColor = 'info';
                }
                $statusHtml = '<span class="badge bg-' . $statusColor . '">' . e($statusLabel) . '</span>';
                if ($submission->isPending() && $submission->hasPendingApproval()) {
                    $statusHtml = '<button type="button" class="badge bg-info border-0 approval-status-badge" '
                        . 'onclick="showBudgetSubmissionApprovalTimeline(' . $submission->id . ')" '
                        . 'title="View approval progress">' . e($statusLabel) . '</button>';
                }

                $typeColor = $submission->type == 'add' ? 'info' : 'secondary';
                $typeLabel = $submission->type == 'add' ? 'Add Budget' : 'Relocation';

                $html .= '<tr>';
                $html .= '<td>' . $no++ . '</td>';
                $html .= '<td>' . e($submission->submission_date->format('d/m/Y')) . '</td>';
                $html .= '<td>' . e($submission->division->name ?? '-') . '</td>';
                $html .= '<td><span class="badge bg-' . $typeColor . '">' . $typeLabel . '</span></td>';
                $html .= '<td><small>' . e($submission->workPlan->activity ?? '-') . '</small></td>';
                $html .= '<td><small>' . e(Str::limit($submission->description ?? '', 50)) . '</small></td>';
                $html .= '<td class="text-end">Rp ' . number_format($submission->estimation_amount, 0, ',', '.') . '</td>';
                $html .= '<td><small>' . e($submission->budget_account_label) . '</small></td>';
                $html .= '<td>' . $statusHtml . '</td>';
                
                // Action buttons
                $html .= '<td><div class="btn-group" role="group">';

                if ($submission->canBeEdited()) {
                    $html .= '<button type="button" class="btn btn-sm btn-warning" onclick="editSubmission(' . $submission->id . ')" title="Edit">';
                    $html .= '<i class="ri-edit-line"></i></button>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteSubmission(' . $submission->id . ')" title="Delete">';
                    $html .= '<i class="ri-delete-bin-line"></i></button>';
                    $html .= '<button type="button" class="btn btn-sm btn-primary" onclick="submitForApproval(' . $submission->id . ')" title="Submit for Approval">';
                    $html .= '<i class="ri-send-plane-2-line"></i></button>';
                } else {
                    $html .= '<button type="button" class="btn btn-sm btn-info" onclick="viewBudgetSubmissionDetail(' . $submission->id . ')" title="View Detail">';
                    $html .= '<i class="ri-eye-line"></i></button>';
                }

                $html .= '</div></td>';
                $html .= '</tr>';
            }

            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $budgetSubmissions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load table data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approvalTimeline(int $id)
    {
        try {
            $result = $this->budgetSubmissionApprovalService->getApprovalTimelineForSubmission($id);

            return response()->json($result, $result['success'] ? 200 : 404);
        } catch (\Throwable $e) {
            Log::error('BudgetSubmissionController.approvalTimeline error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat approval timeline: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitForApproval(int $id)
    {
        try {
            $result = $this->budgetSubmissionApprovalService->submitForApproval($id);

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            Log::error('BudgetSubmissionController.submitForApproval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan approval: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approveDetail(Request $request, int $detailId)
    {
        try {
            $request->validate([
                'comments' => 'nullable|string|max:1000',
                'source_budget_account_id' => 'nullable|integer|exists:workplan_budget_items,id',
            ]);

            $employmentId = $this->getEmploymentIdForCurrentUser();
            if (! $employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ], 403);
            }

            $result = $this->budgetSubmissionApprovalService->processApproval(
                $detailId,
                'approve',
                $employmentId,
                $request->input('comments'),
                $request->filled('source_budget_account_id')
                    ? $request->integer('source_budget_account_id')
                    : null
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            Log::error('BudgetSubmissionController.approveDetail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rejectDetail(Request $request, int $detailId)
    {
        try {
            $request->validate([
                'comments' => 'required|string|max:1000',
            ]);

            $employmentId = $this->getEmploymentIdForCurrentUser();
            if (! $employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ], 403);
            }

            $result = $this->budgetSubmissionApprovalService->processApproval(
                $detailId,
                'reject',
                $employmentId,
                $request->input('comments')
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            Log::error('BudgetSubmissionController.rejectDetail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses penolakan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkProcessApprovals(Request $request)
    {
        try {
            $request->validate([
                'detail_ids' => 'required|array|min:1',
                'detail_ids.*' => 'integer|exists:approval_request_details,id',
                'action' => 'required|in:approve,reject',
                'comments' => 'nullable|string|max:1000',
            ]);

            if ($request->input('action') === 'reject' && ! $request->filled('comments')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan penolakan wajib diisi untuk bulk reject.',
                ], 422);
            }

            $employmentId = $this->getEmploymentIdForCurrentUser();
            if (! $employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ], 403);
            }

            $result = $this->budgetSubmissionApprovalService->bulkProcessApproval(
                $request->input('detail_ids'),
                $request->input('action'),
                $employmentId,
                $request->input('comments')
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            Log::error('BudgetSubmissionController.bulkProcessApprovals error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk approval: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pendingApprovals()
    {
        $employmentId = $this->getEmploymentIdForCurrentUser();
        if (! $employmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Data employment Anda tidak ditemukan.',
            ], 403);
        }

        return response()->json(
            $this->budgetSubmissionApprovalService->getPendingApprovalsForUser($employmentId)
        );
    }

    public function approvedApprovals()
    {
        $employmentId = $this->getEmploymentIdForCurrentUser();
        if (! $employmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Data employment Anda tidak ditemukan.',
            ], 403);
        }

        return response()->json(
            $this->budgetSubmissionApprovalService->getApprovedApprovalsForUser($employmentId)
        );
    }

    public function show($id)
    {
        try {
            $data = $this->budgetSubmissionService->show((int) $id);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load budget submission detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreBudgetSubmissionRequest $request)
    {
        try {
            $user = Auth::user();
            $data = BudgetSubmissionData::fromArray($request->validated());
            
            $this->budgetSubmissionService->store($data, $user);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget submission created successfully.'
                ]);
            }
            
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission created successfully.');
        } catch (DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create budget submission: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create budget submission: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $data = $this->budgetSubmissionService->edit($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load submission data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateBudgetSubmissionRequest $request, $id)
    {
        try {
            $data = BudgetSubmissionData::fromArray($request->validated());
            
            $this->budgetSubmissionService->update($id, $data);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget submission updated successfully.'
                ]);
            }
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission updated successfully.');
        } catch (DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget submission not found. It may have been deleted.'
                ], 404);
            }
            return redirect()->route('budget.submission.index')
                ->with('error', 'Budget submission not found. It may have been deleted.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update budget submission: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('budget.submission.index')
                ->with('error', 'Failed to update budget submission: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->budgetSubmissionService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Budget submission deleted successfully.'
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been already deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $this->budgetSubmissionService->approve($id);

            return response()->json([
                'success' => true,
                'message' => 'Budget submission approved successfully.'
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $this->budgetSubmissionService->reject($id);

            return response()->json([
                'success' => true,
                'message' => 'Budget submission rejected successfully.'
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workplans filtered by division and current year
     */
    public function getWorkPlansByDivision(Request $request)
    {
        return response()->json(
            $this->budgetSubmissionService->getWorkPlansByDivision($request->integer('division_id') ?: null)
        );
    }

    /**
     * Get workplan budget items for dropdown (legacy route name retained).
     */
    public function getAllBudgetCodes(Request $request)
    {
        return response()->json(
            $this->budgetSubmissionService->getBudgetItemsForDropdown($request->all())
        );
    }

    /**
     * Get workplan budget items with Select2-compatible response (legacy route name retained).
     */
    public function getBudgetCodes(Request $request)
    {
        $payload = $this->budgetSubmissionService->getBudgetItemsForDropdown($request->all());
        $results = collect($payload['data'] ?? [])->map(fn ($item) => [
            'id' => $item['value'],
            'text' => $item['label'],
        ]);

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $payload['has_more'] ?? false,
            ],
        ]);
    }

    protected function getEmploymentIdForCurrentUser(): ?int
    {
        $employee = Auth::user();

        return $employee?->employment?->id;
    }
}
