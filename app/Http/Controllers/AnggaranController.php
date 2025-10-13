<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnggaranController extends Controller
{
    public function index()
    {
        $title = 'Anggaran';
        return view('pages.Anggaran', compact('title'));
    }

    public function create()
    {
        // Menampilkan form tambah produk
        $title = 'Input Anggaran';
        return view('pages.Anggaran_Create', compact('title'));
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
        $title = 'Edit Anggaran';
        return view('pages.Anggaran_Edit', compact('title'));
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
