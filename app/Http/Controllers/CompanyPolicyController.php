<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyPolicyController extends Controller
{
    public function index()
    {
        $title = 'Company Policy';
        return view('pages.CompanyPolicy', compact('title'));
    }

    public function create()
    {
        // Menampilkan form tambah company policy
        $title = 'Add Company Policy';
        return view('pages.CompanyPolicy_Create', compact('title'));
    }

    public function store(Request $request)
    {
        // Menyimpan company policy baru
    }

    public function show($id)
    {
        // Menampilkan detail company policy
    }

    public function edit($id)
    {
        // Menampilkan form edit company policy
        $title = 'Edit Company Policy';
        return view('pages.CompanyPolicy_Edit', compact('title'));
    }

    public function update(Request $request, $id)
    {
        // Update data company policy
    }

    public function destroy($id)
    {
        // Hapus company policy
    }
}
