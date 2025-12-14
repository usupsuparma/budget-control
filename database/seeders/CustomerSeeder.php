<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('customer')->insert([
            [
                'customer'   => 'PT AKR',
                'address'    => '',
                'callSign'   => 'AKR',
                'notes'      => '',
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT SKA',
                'address'    => '',
                'callSign'   => 'SKA',
                'notes'      => '',
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT IATM',
                'address'    => '',
                'callSign'   => 'IATM',
                'notes'      => '',
                'status'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT RICH',
                'address'    => '',
                'callSign'   => 'RICH',
                'notes'      => '',
                'status'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT PMMK',
                'address'    => '',
                'callSign'   => 'PMMK',
                'notes'      => '',
                'status'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT AC',
                'address'    => '',
                'callSign'   => 'AC',
                'notes'      => '',
                'status'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT EXP',
                'address'    => '',
                'callSign'   => 'EXP',
                'notes'      => '',
                'status'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'customer'   => 'PT PIP',
                'address'    => '',
                'callSign'   => 'PIP',
                'notes'      => '',
                'status'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Tambahkan data lain di sini...
        ]);
    }
}
