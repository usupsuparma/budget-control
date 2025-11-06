<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RealisasiController extends Controller
{
    public function index()
    {
        $title = 'Realisasi';
        return view('pages.Realisasi', compact('title'));
    }

    public function index_unitkerja()
    {
        $title = 'Realisasi';
        return view('pages.UnitKerjaRealisasi', compact('title'));
    }

    public function create()
    {
        // Menampilkan form tambah produk
        $title = 'Input Realisasi';
        return view('pages.Realisasi_Create', compact('title'));
    }

    public function store(Request $request)
    {
        // Menyimpan produk baru
    }

    public function show($id)
    {
        // Menampilkan detail produk
    }

    public function edit($id)
    {
        // Menampilkan form edit produk
        $title = 'Edit Realisasi';
        return view('pages.Realisasi_Edit', compact('title'));
    }

    public function update(Request $request, $id)
    {
        // Update data produk
    }

    public function destroy($id)
    {
        // Hapus produk
    }
}
