<?php

namespace App\Services\PipIntegrationService;

use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerData;
use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerResponseData;
use App\Services\PipIntegrationService\DTOs\PipApiResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PipIntegrationServiceImpl implements PipIntegrationService
{
    private string $baseUrl;
    private ?string $apiToken;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.pip.base_url', ''), '/');
        $this->apiToken = config('services.pip.api_token');
    }

    /**
     * {@inheritdoc}
     */
    public function getPph(): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_pph.php'));
    }

    /**
     * {@inheritdoc}
     */
    public function getTax(?string $keyword = null): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_tax.php', $keyword ? ['keyword' => $keyword] : []));
    }

    /**
     * {@inheritdoc}
     */
    public function getPpn(?string $keyword = null): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_ppn.php', $keyword ? ['keyword' => $keyword] : []));
    }

    /**
     * {@inheritdoc}
     */
    public function getVendor(?string $keyword = null): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_vendor.php', $keyword ? ['keyword' => $keyword] : []));
    }

    /**
     * {@inheritdoc}
     */
    public function getJenisTransaksi(?string $keyword = null): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_jenis_transaksi.php', $keyword ? ['keyword' => $keyword] : []));
    }

    /**
     * {@inheritdoc}
     */
    public function getJenisKas(?string $keyword = null): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_jenis_kas.php', $keyword ? ['keyword' => $keyword] : []));
    }

    /**
     * {@inheritdoc}
     */
    public function getCostCenter(?string $keyword = null): PipApiResponse
    {
        return PipApiResponse::fromArray($this->get('/get_cost_center.php', $keyword ? ['keyword' => $keyword] : []));
    }

    /**
     * {@inheritdoc}
     */
    public function submitPengeluaranReguler(PengeluaranRegulerData $data): PengeluaranRegulerResponseData
    {
        try {
            $payload = array_filter([
                'tgl'        => $data->tgl,
                'jenis_kas'  => $data->jenisKas,
                'currency'   => $data->currency,
                'rate'       => (int) $data->rate,
                'keterangan' => $data->keterangan,
                'budget'     => $data->budget,
                'items'      => array_map(
                    fn($item) => $item instanceof \App\Services\PipIntegrationService\DTOs\PengeluaranRegulerItemData
                        ? $item->toArray()
                        : $item,
                    $data->items
                ),
            ], fn($v) => $v !== null);

            Log::info('Submitting pengeluaran reguler to PIP', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . ($this->apiToken ?? '')
            ])
                ->post($this->baseUrl . '/api/' . '/pengeluaran_reguler_submit.php', $payload);

            $body = $response->json();

            Log::info('Received response from PIP pengeluaran reguler submission', ['status' => $response->status(), 'body' => $body]);

            if ($response->failed() || ! ($body['success'] ?? false)) {
                Log::warning('PIP submitPengeluaranReguler failed', [
                    'status'   => $response->status(),
                    'response' => $body,
                ]);

                return PengeluaranRegulerResponseData::fromArray([
                    'success' => false,
                    'message' => $body['message'] ?? 'PIP API request failed.',
                    'data'    => [],
                ]);
            }

            return PengeluaranRegulerResponseData::fromArray([
                'success' => true,
                'message' => $body['message'] ?? 'Pengeluaran reguler berhasil disimpan.',
                'data'    => $body['data'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('PIP submitPengeluaranReguler exception', ['error' => $e->getMessage()]);

            return PengeluaranRegulerResponseData::fromArray([
                'success' => false,
                'message' => 'Gagal terhubung ke PIP: ' . $e->getMessage(),
                'data'    => [],
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Perform a GET request to the PIP API and normalise the response.
     *
     * @param  string  $endpoint  Path relative to base URL (e.g. '/get_pph.php')
     * @param  array   $params    Optional query parameters
     * @return array  ['success' => bool, 'data' => array, 'message' => string, 'total' => int]
     */
    private function get(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . ($this->apiToken ?? '')
            ])
                ->get($this->baseUrl . '/api' . $endpoint, $params);
            $body     = $response->json();

            if ($response->failed() || ! ($body['success'] ?? false)) {
                Log::warning("PIP GET {$endpoint} failed", [
                    'status'   => $response->status(),
                    'response' => $body,
                ]);

                return [
                    'success' => false,
                    'message' => $body['message'] ?? 'PIP API request failed.',
                    'data'    => [],
                    'total'   => 0,
                ];
            }

            return [
                'success' => true,
                'message' => $body['message'] ?? 'OK',
                'data'    => $body['data'] ?? [],
                'total'   => $body['total'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("PIP GET {$endpoint} exception", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke PIP: ' . $e->getMessage(),
                'data'    => [],
                'total'   => 0,
            ];
        }
    }
}
