<?php

namespace App\Http\Controllers;

use App\DTOs\KPIDivisionData;
use App\Exceptions\DomainException;
use App\Http\Requests\InlineUpdateKPIDivisionRequest;
use App\Http\Requests\KPIDivisionDataTableRequest;
use App\Http\Requests\StoreKPIDivisionRequest;
use App\Http\Requests\UpdateKPIDivisionRequest;
use App\Services\KPIDivisionService\KPIDivisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KPIDivisionController extends Controller
{
    public function __construct(private KPIDivisionService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->service->getIndexData();
        $data['kpiDivisionUrls'] = [
            'datatable' => route('kpidivision.datatable'),
            'store' => route('kpidivision.store'),
            'show' => route('kpidivision.show', ['id' => ':id']),
            'update' => route('kpidivision.update', ['id' => ':id']),
            'destroy' => route('kpidivision.destroy', ['id' => ':id']),
            'inline' => route('kpidivision.inline', ['id' => ':id']),
        ];

        return view('pages.kpi.division_rev1', $data);
    }

    public function dataTable(KPIDivisionDataTableRequest $request): JsonResponse
    {
        try {
            $year = $request->validated()['year'] ?? null;
            $rows = $this->service->getDataTableRows($year);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
                'data' => $rows,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => null,
            ], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKPIDivisionRequest $request): JsonResponse
    {
        try {
            $data = KPIDivisionData::fromArray($request->validated());
            $kpi = $this->service->create($data);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Division row created successfully.',
                'data' => ['id' => $kpi->id],
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $kpi = $this->service->find((int) $id);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
                'data' => $kpi,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => null,
            ], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKPIDivisionRequest $request, $id): JsonResponse
    {
        try {
            $data = KPIDivisionData::fromArray($request->validated());
            $kpi = $this->service->update((int) $id, $data);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Division row updated successfully.',
                'data' => ['id' => $kpi->id],
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => null,
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete((int) $id);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Division berhasil dihapus.',
                'data' => ['id' => (int) $id],
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => null,
            ], 500);
        }
    }

    public function inlineUpdate(InlineUpdateKPIDivisionRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->service->inlineUpdate(
                (int) $id,
                $validated['field'],
                $validated['value'] ?? null
            );

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diperbarui.',
                'data' => [
                    'field' => $validated['field'],
                    'value' => $result['value'],
                    'display_value' => $result['display_value'],
                ],
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => null,
            ], 500);
        }
    }
}
