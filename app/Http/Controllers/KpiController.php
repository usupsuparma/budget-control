<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function index()
    {
        $title = 'KPI';
        return view('pages.Kpi', compact('title'));
    }

    public function create()
    {
        // Menampilkan form tambah produk
        $title = 'Input KPI';
        return view('pages.Kpi_Create', compact('title'));
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
        $title = 'Edit KPI';
        return view('pages.Kpi_Edit', compact('title'));
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
