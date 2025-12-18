<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\ProductionDetail;
use App\Models\Division;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductionImport;
use App\Exports\ProductionTemplateExport;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $divisions = Division::all();
        $rows = Production::with('details')->orderByDesc('id')->get();
        return view('pages.sales-plan.production-plan', compact('rows','divisions'));
    }

    public function create()
    {
        $types = Production::TYPES;
        return view('pages.sales-plan.production.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:'.implode(',', Production::TYPES)],
            'production' => ['required','string','max:255'],
            'year' => ['nullable','integer'],
            'details' => ['required','array','min:1'],
            'details.*.detail' => ['required','string'],
            'details.*.jan' => ['nullable','numeric'],
            'details.*.feb' => ['nullable','numeric'],
            'details.*.mar' => ['nullable','numeric'],
            'details.*.apr' => ['nullable','numeric'],
            'details.*.may' => ['nullable','numeric'],
            'details.*.jun' => ['nullable','numeric'],
            'details.*.jul' => ['nullable','numeric'],
            'details.*.aug' => ['nullable','numeric'],
            'details.*.sep' => ['nullable','numeric'],
            'details.*.oct' => ['nullable','numeric'],
            'details.*.nov' => ['nullable','numeric'],
            'details.*.dec' => ['nullable','numeric'],
        ]);

        $p = Production::create($request->only('type','production','year'));

        foreach ($request->details as $d) {
            $months = array_map(fn($k)=> (float)($d[$k] ?? 0), ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec']);
            $total = array_sum($months);

            $p->details()->create([
                'detail' => $d['detail'],
                'jan'=>$d['jan'] ?? 0, 'feb'=>$d['feb'] ?? 0, 'mar'=>$d['mar'] ?? 0, 'apr'=>$d['apr'] ?? 0,
                'may'=>$d['may'] ?? 0, 'jun'=>$d['jun'] ?? 0, 'jul'=>$d['jul'] ?? 0, 'aug'=>$d['aug'] ?? 0,
                'sep'=>$d['sep'] ?? 0, 'oct'=>$d['oct'] ?? 0, 'nov'=>$d['nov'] ?? 0, 'dec'=>$d['dec'] ?? 0,
                'total'=>$total,
            ]);
        }

        return redirect()->route('production.index')->with('success','Saved');
    }

    public function edit(Production $production)
    {
        $production->load('details');
        $types = Production::TYPES;
        return view('pages.sales-plan.production.edit', compact('production','types'));
    }

    public function update(Request $request, Production $production)
    {
        $request->validate([
            'type' => ['required', 'in:'.implode(',', Production::TYPES)],
            'production' => ['required','string','max:255'],
            'year' => ['nullable','integer'],
            'details' => ['required','array','min:1'],
            'details.*.detail' => ['required','string'],
            'details.*.jan' => ['nullable','numeric'],
            'details.*.feb' => ['nullable','numeric'],
            'details.*.mar' => ['nullable','numeric'],
            'details.*.apr' => ['nullable','numeric'],
            'details.*.may' => ['nullable','numeric'],
            'details.*.jun' => ['nullable','numeric'],
            'details.*.jul' => ['nullable','numeric'],
            'details.*.aug' => ['nullable','numeric'],
            'details.*.sep' => ['nullable','numeric'],
            'details.*.oct' => ['nullable','numeric'],
            'details.*.nov' => ['nullable','numeric'],
            'details.*.dec' => ['nullable','numeric'],
        ]);

        $production->update($request->only('type','production','year'));

        // simple strategy: replace all detail rows
        $production->details()->delete();

        foreach ($request->details as $d) {
            $months = array_map(fn($k)=> (float)($d[$k] ?? 0), ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec']);
            $total = array_sum($months);

            $production->details()->create([
                'detail' => $d['detail'],
                'jan'=>$d['jan'] ?? 0, 'feb'=>$d['feb'] ?? 0, 'mar'=>$d['mar'] ?? 0, 'apr'=>$d['apr'] ?? 0,
                'may'=>$d['may'] ?? 0, 'jun'=>$d['jun'] ?? 0, 'jul'=>$d['jul'] ?? 0, 'aug'=>$d['aug'] ?? 0,
                'sep'=>$d['sep'] ?? 0, 'oct'=>$d['oct'] ?? 0, 'nov'=>$d['nov'] ?? 0, 'dec'=>$d['dec'] ?? 0,
                'total'=>$total,
            ]);
        }

        return redirect()->route('production.index')->with('success','Updated');
    }

    public function destroy(Production $production)
    {
        $production->delete();
        return back()->with('success','Deleted');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new ProductionImport, $request->file('file'));

        return back()->with('success','Import success');
    }

    public function template()
    {
        return Excel::download(new ProductionTemplateExport, 'production_import_template.xlsx');
    }

    // app/Http/Controllers/ProductionController.php
    public function json(Production $production)
    {
        $production->load('details');
        return response()->json([
            'status' => 'success',
            'data' => $production,
        ]);
    }

}
