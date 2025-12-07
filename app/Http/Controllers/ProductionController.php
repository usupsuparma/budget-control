<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::all();

        return view('pages.sales-plan.production-plan', compact('divisions'));
    }
}
