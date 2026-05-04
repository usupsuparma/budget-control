<?php

namespace Tests\Unit\Services;

use App\Services\PipIntegrationService\PipIntegrationService;
use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerData;
use App\Services\PipIntegrationService\DTOs\PengeluaranRegulerItemData;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PipIntegrationServiceTest extends TestCase
{
    private PipIntegrationService $service;
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PipIntegrationService::class);
        $this->baseUrl = rtrim(config('services.pip.base_url', 'http://localhost/pip_new/api'), '/');
    }

    // ─────────────────────────────────────────────────────────────────────
    // getPph
    // ─────────────────────────────────────────────────────────────────────

    public function test_get_pph_returns_success_with_data(): void
    {
        Http::fake([
            $this->baseUrl . '/get_pph.php' => Http::response([
                'success' => true,
                'message' => 'Data PPH berhasil diambil',
                'total'   => 1,
                'data'    => [['id' => 1, 'nama' => '0%', 'pph' => 0, 'label' => '0%']],
            ], 200),
        ]);

        $result = $this->service->getPph();

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertSame(1, $result['total']);
    }

    public function test_get_pph_returns_failure_on_api_error(): void
    {
        Http::fake([
            $this->baseUrl . '/get_pph.php' => Http::response([
                'success' => false,
                'message' => 'Server error',
                'data'    => [],
            ], 200),
        ]);

        $result = $this->service->getPph();

        $this->assertFalse($result['success']);
        $this->assertEmpty($result['data']);
    }

    public function test_get_pph_returns_failure_on_http_500(): void
    {
        Http::fake([
            $this->baseUrl . '/get_pph.php' => Http::response([], 500),
        ]);

        $result = $this->service->getPph();

        $this->assertFalse($result['success']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // getTax
    // ─────────────────────────────────────────────────────────────────────

    public function test_get_tax_with_keyword_returns_filtered_data(): void
    {
        Http::fake([
            $this->baseUrl . '/get_tax.php*' => Http::response([
                'success' => true,
                'message' => 'Data tax berhasil diambil',
                'total'   => 1,
                'data'    => [['id' => 6, 'code' => 'pph21', 'tax' => 'PPH 21', 'label' => 'PPH 21']],
            ], 200),
        ]);

        $result = $this->service->getTax('pph');

        $this->assertTrue($result['success']);
        $this->assertSame('pph21', $result['data'][0]['code']);
    }

    public function test_get_tax_without_keyword_returns_data(): void
    {
        Http::fake([
            $this->baseUrl . '/get_tax.php*' => Http::response([
                'success' => true,
                'message' => 'Data tax berhasil diambil',
                'total'   => 4,
                'data'    => [],
            ], 200),
        ]);

        $result = $this->service->getTax();

        $this->assertTrue($result['success']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // getPpn
    // ─────────────────────────────────────────────────────────────────────

    public function test_get_ppn_returns_data_with_keyword(): void
    {
        Http::fake([
            $this->baseUrl . '/get_ppn.php*' => Http::response([
                'success' => true,
                'message' => 'Data PPN berhasil diambil',
                'total'   => 1,
                'data'    => [['id' => 3, 'nama' => '11 %', 'ppn' => 0.11, 'label' => '11 %']],
            ], 200),
        ]);

        $result = $this->service->getPpn('11');

        $this->assertTrue($result['success']);
        $this->assertSame(0.11, $result['data'][0]['ppn']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // getVendor
    // ─────────────────────────────────────────────────────────────────────

    public function test_get_vendor_returns_filtered_list(): void
    {
        Http::fake([
            $this->baseUrl . '/get_vendor.php*' => Http::response([
                'success' => true,
                'message' => 'Data vendor berhasil diambil',
                'total'   => 1,
                'data'    => [['id' => 31, 'no' => '31110.A0031', 'name' => 'ASE MANDIRI', 'label' => '31110.A0031 - ASE MANDIRI']],
            ], 200),
        ]);

        $result = $this->service->getVendor('mandiri');

        $this->assertTrue($result['success']);
        $this->assertSame('ASE MANDIRI', $result['data'][0]['name']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // getJenisTransaksi
    // ─────────────────────────────────────────────────────────────────────

    public function test_get_jenis_transaksi_returns_list(): void
    {
        Http::fake([
            $this->baseUrl . '/get_jenis_transaksi.php*' => Http::response([
                'success' => true,
                'message' => 'Data jenis transaksi berhasil diambil',
                'total'   => 1,
                'data'    => [['id' => 300, 'kode' => '6000.1.1.01.01', 'jenis_transaksi' => 'Pengeluaran biaya Hydrogen Gas', 'label' => 'Pengeluaran biaya Hydrogen Gas']],
            ], 200),
        ]);

        $result = $this->service->getJenisTransaksi('biaya');

        $this->assertTrue($result['success']);
        $this->assertSame('6000.1.1.01.01', $result['data'][0]['kode']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // getJenisKas
    // ─────────────────────────────────────────────────────────────────────

    public function test_get_jenis_kas_returns_list(): void
    {
        Http::fake([
            $this->baseUrl . '/get_jenis_kas.php*' => Http::response([
                'success' => true,
                'message' => 'Data Jenis Kas berhasil diambil',
                'total'   => 1,
                'data'    => [['id' => 2, 'kode' => '1100.0.0.00.00', 'nama' => 'Cash On Hand And At Bank', 'label' => '1100.0.0.00.00 - Cash On Hand And At Bank']],
            ], 200),
        ]);

        $result = $this->service->getJenisKas('kas');

        $this->assertTrue($result['success']);
        $this->assertSame('Cash On Hand And At Bank', $result['data'][0]['nama']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // submitPengeluaranReguler
    // ─────────────────────────────────────────────────────────────────────

    public function test_submit_pengeluaran_reguler_returns_transaction_data(): void
    {
        Http::fake([
            $this->baseUrl . '/pengeluaran_reguler_submit.php*' => Http::response([
                'success' => true,
                'message' => 'Pengeluaran reguler berhasil disimpan',
                'data'    => [
                    'id_transaksi' => 'BP001009',
                    'doc_type'     => 'BP - Bank Payment',
                    'doc_no'       => 'BP0.005280',
                    'total'        => 3270000,
                    'total_usd'    => 198.18,
                    'items_count'  => 2,
                ],
            ], 200),
        ]);

        $item1 = new PengeluaranRegulerItemData(
            jenisTransaksi:  '1810.3.2.01.04',
            costCenterCode:  'CC001',
            vendorId:        '12',
            value:           1000000,
            reff:            'INV-001',
            ppn:             0.11,
            ppnval:          110000,
            tot:             'PPh23',
            pph:             0.02,
            pphval:          20000,
            taxTrxId:        'TAX-001',
        );
        $item2 = new PengeluaranRegulerItemData(
            jenisTransaksi:  '1810.3.2.01.04',
            costCenterCode:  'CC002',
            vendorId:        '15',
            value:           2000000,
            reff:            'INV-002',
            ppn:             0.11,
            ppnval:          220000,
            tot:             'PPh23',
            pph:             0.02,
            pphval:          40000,
            taxTrxId:        'TAX-002',
        );

        $dto = new PengeluaranRegulerData(
            tgl:        '2026-04-15',
            jenisKas:   '1100.1.1.00.00',
            currency:   'IDR',
            rate:       16500,
            items:      [$item1, $item2],
            keterangan: 'Pembayaran operasional kantor',
        );

        $result = $this->service->submitPengeluaranReguler($dto);

        $this->assertTrue($result['success']);
        $this->assertSame('BP001009', $result['data']['id_transaksi']);
        $this->assertSame('BP0.005280', $result['data']['doc_no']);
    }

    public function test_submit_pengeluaran_reguler_returns_failure_on_api_error(): void
    {
        Http::fake([
            $this->baseUrl . '/pengeluaran_reguler_submit.php*' => Http::response([
                'success' => false,
                'message' => 'Token tidak valid',
                'data'    => [],
            ], 401),
        ]);

        $item = new PengeluaranRegulerItemData(
            jenisTransaksi: '1810.3.2.01.04',
            costCenterCode: 'CC001',
            vendorId:       '12',
            value:          1000000,
        );

        $dto = new PengeluaranRegulerData(
            tgl:      '2026-04-15',
            jenisKas: '1100.1.1.00.00',
            currency: 'IDR',
            rate:     16500,
            items:    [$item],
        );

        $result = $this->service->submitPengeluaranReguler($dto);

        $this->assertFalse($result['success']);
    }

    public function test_submit_pengeluaran_reguler_returns_failure_on_connection_exception(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        $item = new PengeluaranRegulerItemData(
            jenisTransaksi: '1810.3.2.01.04',
            costCenterCode: 'CC001',
            vendorId:       '1',
            value:          500000,
        );

        $dto = new PengeluaranRegulerData(
            tgl:      '2026-04-15',
            jenisKas: '1100.1.1.00.00',
            currency: 'IDR',
            rate:     16500,
            items:    [$item],
        );

        $result = $this->service->submitPengeluaranReguler($dto);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gagal terhubung ke PIP', $result['message']);
    }
}
