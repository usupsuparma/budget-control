<?php

namespace App\Http\Controllers;

use App\DTOs\KPISectionData;
use App\Exceptions\DomainException;
use App\Http\Requests\KPISectionDataTableRequest;
use App\Http\Requests\StoreKPISectionRequest;
use App\Http\Requests\UpdateKPISectionRequest;
use App\Services\KPISectionService\KPISectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KPISectionController extends Controller
{
    public function __construct(private KPISectionService $service) {}

    public function index()
    {
        $data = $this->service->getIndexData();
        $data['kpiSectionUrls'] = [
            'datatable' => route('kpisection.datatable'),
            'store' => route('kpisection.store'),
            'show' => route('kpisection.show', ['id' => ':id']),
            'update' => route('kpisection.update', ['id' => ':id']),
            'destroy' => route('kpisection.destroy', ['kpiSection' => ':id']),
        ];

        return view('pages.kpi.section_rev1', $data);
    }

    /**
     * DataTables AJAX
     */
    public function dataTable(KPISectionDataTableRequest $request): JsonResponse
    {
        try {
            $year = $request->validated()['year'] ?? null;
            $data = $this->service->getDataTableRows($year);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
                'data' => $data,
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

    public function store(StoreKPISectionRequest $request): JsonResponse
    {
        try {
            $data = KPISectionData::fromArray($request->validated());
            $kpi = $this->service->create($data);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Section created successfully.',
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

    public function show($id): JsonResponse
    {
        try {
            $kpi = $this->service->find((int) $id);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
                'data' => $kpi,
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

    public function update(UpdateKPISectionRequest $request, $id): JsonResponse
    {
        try {
            $data = KPISectionData::fromArray($request->validated());
            $kpi = $this->service->update((int) $id, $data);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Section updated successfully.',
                'data' => ['id' => $kpi->id],
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

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete((int) $id);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Section deleted successfully.',
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
}
