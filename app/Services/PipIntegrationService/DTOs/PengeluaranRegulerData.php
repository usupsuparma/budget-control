<?php

namespace App\Services\PipIntegrationService\DTOs;

/**
 * Data Transfer Object for Pengeluaran Reguler (Regular Expenditure) submission to PIP.
 *
 * Maps to POST /pengeluaran_reguler_submit.php with JSON body.
 */
readonly class PengeluaranRegulerData
{
    /**
     * @param  string                          $tgl        Tanggal transaksi (Y-m-d)
     * @param  string                          $jenisKas   Kode jenis kas (e.g. "1100.1.1.00.00")
     * @param  string                          $currency   Mata uang (e.g. "IDR", "USD")
     * @param  float                           $rate       Kurs
     * @param  PengeluaranRegulerItemData[]     $items      Detail item transaksi
     * @param  string|null                     $keterangan Keterangan transaksi
     */
    public function __construct(
        public string  $tgl,
        public string  $jenisKas,
        public string  $currency,
        public float   $rate,
        public array   $items,
        public ?string $keterangan = null,
    ) {}
}
