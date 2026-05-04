<?php

namespace App\Services\PipIntegrationService;

use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerData;

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
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getPph(): array;

    /**
     * Fetch tax options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getTax(?string $keyword = null): array;

    /**
     * Fetch PPN (Pajak Pertambahan Nilai) options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getPpn(?string $keyword = null): array;

    /**
     * Fetch vendor options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getVendor(?string $keyword = null): array;

    /**
     * Fetch jenis transaksi (transaction type) options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getJenisTransaksi(?string $keyword = null): array;

    /**
     * Fetch jenis kas (cash type) options, optionally filtered by keyword.
     *
     * @param  string|null  $keyword  Optional search keyword
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getJenisKas(?string $keyword = null): array;

    /**
     * Submit a pengeluaran reguler (regular expenditure) to the PIP system.
     * Requires a Bearer Token configured via PIP_API_TOKEN.
     *
     * @param  PengeluaranRegulerData  $data  Expenditure payload
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function submitPengeluaranReguler(PengeluaranRegulerData $data): array;
}
