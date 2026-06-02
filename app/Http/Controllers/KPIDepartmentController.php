<?php

namespace App\Http\Controllers;

use App\DTOs\KPIDepartmentData;
use App\Exceptions\DomainException;
use App\Http\Requests\KPIDepartmentDataTableRequest;
use App\Http\Requests\StoreKPIDepartmentRequest;
use App\Http\Requests\UpdateKPIDepartmentRequest;
use App\Services\KPIDepartmentService\KPIDepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KPIDepartmentController extends Controller
{
    public function __construct(private KPIDepartmentService $service) {}
    /**
     * Halaman utama KPI Department.
     * Hanya kirim data untuk dropdown (KPI Division & Department).
     * Data tabel akan di-load via AJAX (dataTable()).
     */
    public function index()
    {
        $data = $this->service->getIndexData();
        $data['kpiDepartmentUrls'] = [
            'datatable'    => route('KPIDepartment.datatable'),
            'store'        => route('KPIDepartment.store'),
            'show'         => route('KPIDepartment.show', ['id' => ':id']),
            'update'       => route('KPIDepartment.update', ['id' => ':id']),
            'destroy'      => route('KPIDepartment.destroy', ['KPIDepartment' => ':id']),
            'kpiDivisions' => route('KPIDepartment.kpiDivisions'),
            'departmentsByDivision' => route('KPIDepartment.departmentsByDivision'),
        ];

        return view('pages.kpi.department_rev1', $data);
    }

    /**
     * AJAX endpoint: return KPI Divisions filtered by year (and user's division if not admin).
     */
    public function getKpiDivisionsForForm(Request $request): JsonResponse
    {
        try {
            $year = (int) ($request->query('year') ?: now()->year);
            $divisions = $this->service->getKpiDivisionsByYear($year);

            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => 'Data berhasil diambil.',
                'data'    => $divisions,
            ]);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Internal Server Error',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * AJAX endpoint: return Departments filtered by KPI Division ID.
     */
    public function getDepartmentsByDivision(Request $request): JsonResponse
    {
        try {
            $kpiDivisionId = $request->query('kpi_division_id');
            $departments = $this->service->getDepartmentsByKpiDivision((int)$kpiDivisionId);

            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => 'Data berhasil diambil.',
                'data'    => $departments,
            ]);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Internal Server Error',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * DataTables AJAX source.
     */
    public function dataTable(KPIDepartmentDataTableRequest $request): JsonResponse
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
     * Store (Create) – dipanggil dari modal Add.
     */
    public function store(StoreKPIDepartmentRequest $request): JsonResponse
    {
        try {
            $data = KPIDepartmentData::fromArray($request->validated());
            $kpiDept = $this->service->create($data);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Department row created successfully.',
                'data' => ['id' => $kpiDept->id],
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
     * Show – untuk isi modal Edit lewat AJAX.
     */
    public function show($id): JsonResponse
    {
        try {
            $kpiDept = $this->service->find((int) $id);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
                'data' => $kpiDept,
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
     * Update – dipanggil dari modal Edit.
     */
    public function update(UpdateKPIDepartmentRequest $request, $id): JsonResponse
    {
        try {
            $data = KPIDepartmentData::fromArray($request->validated());
            $kpiDept = $this->service->update((int) $id, $data);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Department row updated successfully.',
                'data' => ['id' => $kpiDept->id],
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
     * Delete – hapus 1 baris KPI Department.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete((int) $id);

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Department berhasil dihapus.',
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
