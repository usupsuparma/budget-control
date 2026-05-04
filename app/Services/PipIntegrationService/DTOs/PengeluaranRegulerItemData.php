<?php

namespace App\Services\PipIntegrationService\DTOs;

/**
 * Represents a single line item inside PengeluaranRegulerData.
 *
 * Maps to the `items[]` array of POST /pengeluaran_reguler_submit.php.
 */
readonly class PengeluaranRegulerItemData
{
    /**
     * @param  string      $jenisTransaksi  Kode jenis transaksi (e.g. "1810.3.2.01.04")
     * @param  string      $costCenterCode  Kode cost center
     * @param  string      $vendorId        ID vendor (string)
     * @param  float       $value           Nilai transaksi
     * @param  string|null $reff            Referensi invoice/dokumen
     * @param  float|null  $ppn             Persentase PPN
     * @param  float|null  $ppnval          Nilai PPN
     * @param  string|null $tot             Jenis pajak (misal: "PPh23")
     * @param  float|null  $pph             Persentase PPh
     * @param  float|null  $pphval          Nilai PPh
     * @param  string|null $taxTrxId        ID transaksi pajak
     */
    public function __construct(
        public string  $jenisTransaksi,
        public string  $costCenterCode,
        public string  $vendorId,
        public float   $value,
        public ?string $reff       = null,
        public ?float  $ppn        = null,
        public ?float  $ppnval     = null,
        public ?string $tot        = null,
        public ?float  $pph        = null,
        public ?float  $pphval     = null,
        public ?string $taxTrxId   = null,
    ) {}

    /**
     * Serialize to the array shape expected by the PIP API.
     */
    public function toArray(): array
    {
        return array_filter([
            'jenis_transaksi'  => $this->jenisTransaksi,
            'cost_center_code' => $this->costCenterCode,
            'vendor_id'        => $this->vendorId,
            'reff'             => $this->reff,
            'value'            => $this->value,
            'ppn'              => $this->ppn,
            'ppnval'           => $this->ppnval,
            'tot'              => $this->tot,
            'pph'              => $this->pph,
            'pphval'           => $this->pphval,
            'tax_trx_id'       => $this->taxTrxId,
        ], fn($v) => $v !== null);
    }
}
