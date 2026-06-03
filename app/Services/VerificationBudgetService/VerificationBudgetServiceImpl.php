<?php

namespace App\Services\VerificationBudgetService;

use App\Models\Employment;
use App\Models\PriceVerification;
use App\Models\PriceVerificationCode;
use App\Models\WorkplanBudgetApprover;
use App\Models\WorkplanBudgetItem;
use App\Models\WorkplanBudgetVerification;
use App\Services\WorkplanBudgetItemApprovalService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationBudgetServiceImpl implements VerificationBudgetService
{
    protected WorkplanBudgetItemApprovalService $approvalService;
    protected NotificationService $notificationService;

    public function __construct(WorkplanBudgetItemApprovalService $approvalService, NotificationService $notificationService)
    {
        $this->approvalService = $approvalService;
        $this->notificationService = $notificationService;
    }

    /**
     * Submit budget item for verification (Phase 1)
     * Creates snapshot of eligible verifiers based on cost_center mapping
     */
    public function submitForVerification(int $itemId): array
    {
        try {
            $item = WorkplanBudgetItem::findOrFail($itemId);

            // Validate item can be submitted
            if ($item->status !== 'draft') {
                return [
                    'success' => false,
                    'message' => 'Hanya item dengan status draft yang dapat disubmit untuk verifikasi.',
                ];
            }

            if ($item->verification_status === 'pending') {
                return [
                    'success' => false,
                    'message' => 'Item sudah dalam proses verifikasi.',
                ];
            }

            if ($item->verification_status === 'verified') {
                return [
                    'success' => false,
                    'message' => 'Item sudah terverifikasi.',
                ];
            }

            // Validate cost_center exists
            if (empty($item->cost_center)) {
                return [
                    'success' => false,
                    'message' => 'Cost center belum diisi. Silakan edit item terlebih dahulu.',
                ];
            }

            // Get eligible verifiers based on cost_center
            $verifierIds = $this->getVerifiersByCostCenter($item->cost_center);

            if (empty($verifierIds)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ditemukan verifikator untuk cost center: ' . $item->cost_center . '. Silakan hubungi admin untuk mengatur mapping verifikator.',
                ];
            }

            DB::beginTransaction();

            // Clear existing candidates (if any)
            WorkplanBudgetApprover::where('workplan_budget_item_id', $itemId)->delete();

            // Create snapshot of verifier candidates
            foreach ($verifierIds as $verifierId) {
                WorkplanBudgetApprover::create([
                    'workplan_budget_item_id' => $itemId,
                    'verifier_id' => $verifierId,
                    'is_executor' => false,
                ]);

                // Notify each verifier
                $this->notificationService->send(
                    $verifierId,
                    'verification',
                    'Permintaan Verifikasi Budget',
                    "Ada item budget baru yang perlu diverifikasi: {$item->description}"
                );
            }

            // Update item status
            $item->update([
                'verification_status' => 'pending',
            ]);

            DB::commit();

            Log::info('Budget item submitted for verification', [
                'item_id' => $itemId,
                'cost_center' => $item->cost_center,
                'verifier_count' => count($verifierIds),
                'verifier_ids' => $verifierIds,
            ]);

            return [
                'success' => true,
                'message' => 'Item berhasil diajukan untuk verifikasi.',
                'data' => [
                    'item_id' => $itemId,
                    'verifier_count' => count($verifierIds),
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('VerificationBudgetService.submitForVerification', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengajukan verifikasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify budget item (set fix price and auto-submit for approval)
     */
    public function verifyBudget(int $itemId, float $fixPrice, ?string $notes = null): array
    {
        try {
            $item = WorkplanBudgetItem::lockForUpdate()->findOrFail($itemId);
            $employee = Auth::user();
            $verifierId = $employee?->id; // Employee.id (PK)

            if (!$verifierId) {
                return [
                    'success' => false,
                    'message' => 'Employee tidak ditemukan.',
                ];
            }

            // Validate item status
            if ($item->verification_status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Item tidak dalam status pending verification.',
                ];
            }

            // Check if current user is eligible to verify
            $candidate = WorkplanBudgetApprover::where('workplan_budget_item_id', $itemId)
                ->where('verifier_id', $verifierId)
                ->first();

            if (!$candidate) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk memverifikasi item ini.',
                ];
            }

            // Validate fix price
            if ($fixPrice <= 0) {
                return [
                    'success' => false,
                    'message' => 'Harga verifikasi harus lebih dari 0.',
                ];
            }

            DB::beginTransaction();

            // Calculate total quantity from all months
            $totalQty = 0;
            $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            foreach ($months as $month) {
                $totalQty += (int) ($item->{"activity_$month"} ?? 0);
            }

            // Calculate total = price_final × total_qty
            $calculatedTotal = $fixPrice * $totalQty;

            // 1. Create audit log
            WorkplanBudgetVerification::create([
                'workplan_budget_item_id' => $itemId,
                'verifier_id' => $verifierId,
                'submitted_price_estimation' => $item->price_estimation ?? 0,
                'verified_price_total' => $calculatedTotal,
                'notes' => $notes,
            ]);

            // 2. Update item with verified price and calculated total
            $item->update([
                'price_final' => $fixPrice,
                'total' => $calculatedTotal,
                'verification_status' => 'verified',
            ]);

            // 3. Mark executor in candidates
            WorkplanBudgetApprover::where('workplan_budget_item_id', $itemId)
                ->where('verifier_id', $verifierId)
                ->update(['is_executor' => true]);

            DB::commit();

            Log::info('Budget item verified', [
                'item_id' => $itemId,
                'verifier_id' => $verifierId,
                'old_price' => $item->price_estimation,
                'verified_price_final' => $fixPrice,
                'total_qty' => $totalQty,
                'calculated_total' => $calculatedTotal,
            ]);

            // 4. Auto-submit for approval after verification
            $approvalResult = $this->approvalService->submitForApproval($itemId);

            if (!$approvalResult['success']) {
                Log::warning('Auto-submit approval failed after verification', [
                    'item_id' => $itemId,
                    'approval_message' => $approvalResult['message'],
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Verifikasi berhasil, tetapi auto-submit approval gagal: ' . $approvalResult['message'],
                    'data' => [
                        'item_id' => $itemId,
                        'price_final' => $fixPrice,
                        'total_qty' => $totalQty,
                        'calculated_total' => $calculatedTotal,
                        'approval_submitted' => false,
                    ],
                ];
            }

            return [
                'success' => true,
                'message' => 'Verifikasi berhasil dan item telah diajukan untuk approval.',
                'data' => [
                    'item_id' => $itemId,
                    'price_final' => $fixPrice,
                    'total_qty' => $totalQty,
                    'calculated_total' => $calculatedTotal,
                    'approval_submitted' => true,
                    'approval_data' => $approvalResult['data'] ?? null,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('VerificationBudgetService.verifyBudget', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memverifikasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reject verification
     */
    public function rejectVerification(int $itemId, string $notes): array
    {
        try {
            $item = WorkplanBudgetItem::lockForUpdate()->findOrFail($itemId);
            $employee = Auth::user();
            $verifierId = $employee?->id; // Employee.id (PK)

            if (!$verifierId) {
                return [
                    'success' => false,
                    'message' => 'Employee tidak ditemukan.',
                ];
            }

            // Validate item status
            if ($item->verification_status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Item tidak dalam status pending verification.',
                ];
            }

            // Check if current user is eligible to verify
            $candidate = WorkplanBudgetApprover::where('workplan_budget_item_id', $itemId)
                ->where('verifier_id', $verifierId)
                ->first();

            if (!$candidate) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk memverifikasi item ini.',
                ];
            }

            if (empty($notes)) {
                return [
                    'success' => false,
                    'message' => 'Alasan penolakan wajib diisi.',
                ];
            }

            DB::beginTransaction();

            // 1. Create audit log for rejection
            WorkplanBudgetVerification::create([
                'workplan_budget_item_id' => $itemId,
                'verifier_id' => $verifierId,
                'submitted_price_estimation' => $item->price_estimation ?? 0,
                'verified_price_total' => 0, // Rejection
                'notes' => 'REJECTED: ' . $notes,
            ]);

            // 2. Update item status back to draft so user can edit
            $item->update([
                'verification_status' => 'rejected',
                'status' => 'draft', // Allow editing
            ]);

            // 3. Mark executor in candidates
            WorkplanBudgetApprover::where('workplan_budget_item_id', $itemId)
                ->where('verifier_id', $verifierId)
                ->update(['is_executor' => true]);

            DB::commit();

            Log::info('Budget item verification rejected', [
                'item_id' => $itemId,
                'verifier_id' => $verifierId,
                'notes' => $notes,
            ]);

            return [
                'success' => true,
                'message' => 'Verifikasi ditolak. User dapat mengedit dan mengajukan kembali.',
                'data' => [
                    'item_id' => $itemId,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('VerificationBudgetService.rejectVerification', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menolak verifikasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get items pending verification for current user
     */
    public function getMyPendingVerifications(): array
    {
        try {
            $employee = Auth::user();
            $verifierId = $employee?->id; // Employee.id (PK)

            if (!$verifierId) {
                return [
                    'success' => false,
                    'message' => 'Employee tidak ditemukan.',
                    'data' => [],
                ];
            }

            $items = WorkplanBudgetItem::with([
                'workplan',
                'category',
                'verificationCandidates',
            ])
                ->whereHas('verificationCandidates', function ($query) use ($verifierId) {
                    $query->where('verifier_id', $verifierId);
                })
                ->where('verification_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'success' => true,
                'message' => 'Data berhasil dimuat.',
                'data' => $items,
            ];
        } catch (Exception $e) {
            Log::error('VerificationBudgetService.getMyPendingVerifications', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get verification history for current verifier
     */
    public function getMyVerifiedVerifications(): array
    {
        try {
            $employee = Auth::user();
            $verifierId = $employee?->id; // Employee.id (PK)

            if (!$verifierId) {
                return [
                    'success' => false,
                    'message' => 'Employee tidak ditemukan.',
                    'data' => [],
                ];
            }

            $verifications = WorkplanBudgetVerification::with([
                'verifier',
                'workplanBudgetItem.workplan.KPIDepartment.department',
                'workplanBudgetItem.workplan.KPIDepartment.kpiDivision.division',
                'workplanBudgetItem.workplan.kpiSection.section.department',
                'workplanBudgetItem.workplan.kpiSection.section.department.division',
                'workplanBudgetItem.category',
            ])
                ->where('verifier_id', $verifierId)
                ->where('verified_price_total', '>', 0)
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($verification) {
                    $item = $verification->workplanBudgetItem;

                    if (!$item) {
                        return null;
                    }

                    $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
                    $totalQty = 0;
                    $monthlyData = [];
                    foreach ($months as $m) {
                        $val = (int) ($item->{"activity_$m"} ?? 0);
                        $totalQty += $val;
                        $monthlyData[$m] = $val;
                    }

                    $divisionName = null;
                    $departmentName = null;
                    if ($item->workplan) {
                        if ($item->workplan->kpi_type === 'department' && $item->workplan->KPIDepartment) {
                            $departmentName = $item->workplan->KPIDepartment->department?->name;
                            $divisionName = $item->workplan->KPIDepartment->kpiDivision?->division?->name;
                        } elseif ($item->workplan->kpi_type === 'section' && $item->workplan->kpiSection) {
                            $departmentName = $item->workplan->kpiSection->section?->department?->name;
                            $divisionName = $item->workplan->kpiSection->section?->department?->division?->name;
                        }
                    }

                    $verifiedUnitPrice = $totalQty > 0
                        ? (float) $verification->verified_price_total / $totalQty
                        : 0;

                    return [
                        'verification_id' => $verification->id,
                        'item_id' => $item->id,
                        'reference_number' => $item->budget_code,
                        'verified_at' => $verification->created_at?->format('Y-m-d H:i:s'),
                        'notes' => $verification->notes,
                        'item' => [
                            'id' => $item->id,
                            'description' => $item->description,
                            'category_type' => $item->category_type,
                            'category_name' => $item->category?->name,
                            'stock_code' => $item->stock_code,
                            'budget_code' => $item->budget_code,
                            'cost_center' => $item->cost_center,
                            'supplier_name' => $item->supplier_name,
                            'unit_name' => $item->unit_name,
                            'cons_rate' => $item->cons_rate,
                            'price_estimation' => $item->price_estimation,
                            'price_final' => $item->price_final,
                            'verification_status' => $item->verification_status,
                            'total_qty' => $totalQty,
                            'unit_price' => $verifiedUnitPrice,
                            'total_budget' => (float) $verification->verified_price_total,
                            'monthly' => $monthlyData,
                            'workplan_activity' => $item->workplan?->activity,
                            'workplan_year' => $item->workplan?->year,
                            'division_name' => $divisionName,
                            'department_name' => $departmentName,
                        ],
                    ];
                })
                ->filter()
                ->values();

            return [
                'success' => true,
                'message' => 'Data berhasil dimuat.',
                'data' => $verifications->toArray(),
                'count' => $verifications->count(),
            ];
        } catch (Exception $e) {
            Log::error('VerificationBudgetService.getMyVerifiedVerifications', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
                'data' => [],
                'count' => 0,
            ];
        }
    }

    /**
     * Get verification status for an item
     */
    public function getVerificationStatus(int $itemId): array
    {
        try {
            $item = WorkplanBudgetItem::with([
                'verificationCandidates.verifier',
                'verificationCandidates.verifierEmployment',
                'verifications.verifier',
                'executor.verifier',
            ])->findOrFail($itemId);

            return [
                'success' => true,
                'data' => [
                    'item_id' => $itemId,
                    'verification_status' => $item->verification_status,
                    'price_estimation' => $item->price_estimation,
                    'total' => $item->total,
                    'candidates' => $item->verificationCandidates->map(function ($candidate) {
                        return [
                            'verifier_id' => $candidate->verifier_id,
                            'verifier_name' => $candidate->verifier?->name ?? $candidate->verifierEmployment?->job_position_name,
                            'is_executor' => $candidate->is_executor,
                        ];
                    }),
                    'history' => $item->verifications->map(function ($verification) {
                        return [
                            'verifier_id' => $verification->verifier_id,
                            'verifier_name' => $verification->verifier?->name,
                            'submitted_price' => $verification->submitted_price_estimation,
                            'verified_price' => $verification->verified_price_total,
                            'notes' => $verification->notes,
                            'created_at' => $verification->created_at,
                        ];
                    }),
                    'executor' => $item->executor ? [
                        'verifier_id' => $item->executor->verifier_id,
                        'verifier_name' => $item->executor->verifier?->name,
                    ] : null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('VerificationBudgetService.getVerificationStatus', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memuat status verifikasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get eligible verifiers by cost_center code
     * 
     * Logic:
     * 1. Find PriceVerification -> PriceVerificationCode where inchargecode matches cost_center
     * 2. Get job_position_ids from PriceVerificationUser
     * 3. Find all employees with those job positions
     */
    public function getVerifiersByCostCenter(string $costCenter): array
    {
        try {
            Log::info('Getting verifiers for cost_center', [
                'cost_center' => $costCenter,
            ]);
            // 1. Find price_verification_id from code mapping
            $verificationCode = PriceVerificationCode::where('inchargecode', $costCenter)->first();
            Log::info('Found verification code', [
                'cost_center' => $costCenter,
                'verification_code' => $verificationCode,
            ]);

            if (!$verificationCode) {
                Log::warning('No verification code found for cost_center', [
                    'cost_center' => $costCenter,
                ]);
                return [];
            }

            $priceVerificationId = $verificationCode->price_verification_id;
            Log::info('Found price_verification_id', [
                'cost_center' => $costCenter,
                'price_verification_id' => $priceVerificationId,
            ]);

            // 2. Get job_position_ids that can verify this cost_center
            $priceVerification = PriceVerification::with('users.jobPosition')
                ->find($priceVerificationId);

            if (!$priceVerification || $priceVerification->users->isEmpty()) {
                Log::warning('No verifier users configured for verification', [
                    'price_verification_id' => $priceVerificationId,
                ]);
                return [];
            }

            $jobPositionIds = $priceVerification->users->pluck('job_position_id')->toArray();

            // 3. Find all active employees with those job positions
            // employment.employee_id is now FK to employee.id
            $verifierIds = Employment::whereIn('job_position_id', $jobPositionIds)
                ->where('status', 'active')
                ->whereNotNull('employee_id')
                ->pluck('employee_id') // This is employee.id (integer FK)
                ->toArray();

            Log::info('Found verifiers for cost_center', [
                'cost_center' => $costCenter,
                'price_verification_id' => $priceVerificationId,
                'job_position_ids' => $jobPositionIds,
                'verifier_ids' => $verifierIds,
            ]);

            return $verifierIds;
        } catch (Exception $e) {
            Log::error('VerificationBudgetService.getVerifiersByCostCenter', [
                'cost_center' => $costCenter,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Bulk verify budget items
     */
    public function bulkVerify(array $itemIds, array $fixPrices = [], ?string $notes = null): array
    {
        try {
            DB::beginTransaction();
            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($itemIds as $itemId) {
                // Determine fix price for this item
                // If fixPrices is a simple value (not array), use it for all
                // If fixPrices is an array, check if it has the itemId as key
                $fixPrice = 0;
                if (!is_array($fixPrices)) {
                    $fixPrice = (float) $fixPrices;
                } elseif (isset($fixPrices[$itemId])) {
                    $fixPrice = (float) $fixPrices[$itemId];
                } else {
                    // Fallback: try to find the item to see its current price estimation
                    $item = WorkplanBudgetItem::find($itemId);
                    $fixPrice = (float) ($item->price_estimation ?? 0);
                }

                if ($fixPrice <= 0) {
                    $results[] = [
                        'item_id' => $itemId,
                        'success' => false,
                        'message' => 'Harga verifikasi tidak valid (harus > 0).',
                    ];
                    $failCount++;
                    continue;
                }

                $verifyResult = $this->verifyBudget($itemId, $fixPrice, $notes);
                
                if ($verifyResult['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $results[] = [
                    'item_id' => $itemId,
                    'success' => $verifyResult['success'],
                    'message' => $verifyResult['message'],
                ];
            }

            DB::commit();

            return [
                'success' => $successCount > 0,
                'message' => "Proses bulk verifikasi selesai. Berhasil: $successCount, Gagal: $failCount.",
                'data' => [
                    'results' => $results,
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('VerificationBudgetService.bulkVerify', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses bulk verifikasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk reject verification
     */
    public function bulkReject(array $itemIds, string $notes): array
    {
        try {
            DB::beginTransaction();
            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($itemIds as $itemId) {
                $rejectResult = $this->rejectVerification($itemId, $notes);
                
                if ($rejectResult['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $results[] = [
                    'item_id' => $itemId,
                    'success' => $rejectResult['success'],
                    'message' => $rejectResult['message'],
                ];
            }

            DB::commit();

            return [
                'success' => $successCount > 0,
                'message' => "Proses bulk reject selesai. Berhasil: $successCount, Gagal: $failCount.",
                'data' => [
                    'results' => $results,
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('VerificationBudgetService.bulkReject', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses bulk reject: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process CSV import for bulk verification
     */
    public function processCsvImport($file): array
    {
        try {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle, 1000, ','); // Assume comma separator
            
            // Expected columns: item_id, verified_price
            $idIdx = array_search('item_id', $header);
            $priceIdx = array_search('verified_price', $header);

            if ($idIdx === false || $priceIdx === false) {
                fclose($handle);
                return [
                    'success' => false,
                    'message' => 'Format CSV salah. Pastikan kolom "item_id" dan "verified_price" tersedia.',
                ];
            }

            $itemIds = [];
            $fixPrices = [];
            $count = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $itemId = (int) $data[$idIdx];
                $price = (float) str_replace(',', '', $data[$priceIdx]); // Handle possible thousand separators

                if ($itemId > 0 && $price > 0) {
                    $itemIds[] = $itemId;
                    $fixPrices[$itemId] = $price;
                    $count++;
                }
            }
            fclose($handle);

            if ($count === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data valid yang ditemukan di file CSV.',
                ];
            }

            return $this->bulkVerify($itemIds, $fixPrices, 'Verified via CSV Import');
        } catch (Exception $e) {
            Log::error('VerificationBudgetService.processCsvImport', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses file CSV: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if current user can verify an item
     */
    public function canVerify(int $itemId): bool
    {
        try {
            $employee = Auth::user();
            $verifierId = $employee?->id; // Employee.id (PK)

            if (!$verifierId) {
                return false;
            }

            $item = WorkplanBudgetItem::find($itemId);
            if (!$item || $item->verification_status !== 'pending') {
                return false;
            }

            return WorkplanBudgetApprover::where('workplan_budget_item_id', $itemId)
                ->where('verifier_id', $verifierId)
                ->exists();
        } catch (Exception $e) {
            Log::error('VerificationBudgetService.canVerify', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
