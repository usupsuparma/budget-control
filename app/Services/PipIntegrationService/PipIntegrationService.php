<?php

namespace App\Services\PipIntegrationService;

use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerData;
use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerResponseData;
use App\Services\PipIntegrationService\DTOs\PipApiResponse;

/**
 * Service contract for integration with PIP external application.
 *
 * All methods map directly to PIP API endpoints:
 * Base URL is driven by PIP_API_URL environment variable.
 */
interface PipIntegrationService
{
    /**
     * Fetch all available PPH (Pajak Penghasilan) options.
     *
     * @return PipApiResponse ['success', 'data' => [['id','nama','pph','label']], 'total']
     */
    public function getPph(): PipApiResponse;

    /**
     * Fetch tax options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return PipApiResponse ['success', 'data' => [['id','code','tax','label']], 'total']
     */
    public function getTax(?string $keyword = null): PipApiResponse;

    /**
     * Fetch PPN (Pajak Pertambahan Nilai) options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return PipApiResponse ['success', 'data' => [['id','nama','ppn','label']], 'total']
     */
    public function getPpn(?string $keyword = null): PipApiResponse;

    /**
     * Fetch vendor options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return PipApiResponse ['success', 'data' => [['id','no','name','label']], 'total']
     */
    public function getVendor(?string $keyword = null): PipApiResponse;

    /**
     * Fetch jenis transaksi (transaction type) options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return PipApiResponse ['success', 'data' => [['id','kode','jenis_transaksi','label']], 'total']
     */
    public function getJenisTransaksi(?string $keyword = null): PipApiResponse;

    /**
     * Fetch jenis kas (cash type) options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return PipApiResponse ['success', 'data' => [['id','kode','nama','label']], 'total']
     */
    public function getJenisKas(?string $keyword = null): PipApiResponse;

    /**
     * Fetch cost center options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return PipApiResponse ['success', 'data' => [['id','code','name','dimension','pc','label']], 'total']
     */
    public function getCostCenter(?string $keyword = null): PipApiResponse;

    /**
     * Submit a pengeluaran reguler (regular expenditure) to the PIP system.
     * Requires a Bearer Token configured via PIP_API_TOKEN.
     *
     * @param  PengeluaranRegulerData  $data  Expenditure payload
     * @return PengeluaranRegulerResponseData
     */
    public function submitPengeluaranReguler(PengeluaranRegulerData $data): PengeluaranRegulerResponseData;
}
