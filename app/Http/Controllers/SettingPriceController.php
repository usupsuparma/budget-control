<?php

namespace App\Http\Controllers;

use App\Models\BudgetCode;
use App\Models\Division;
use App\Models\JobPosition;
use App\Models\PriceVerification;
use App\Models\PriceVerificationCode;
use App\Models\PriceVerificationUser;
use Illuminate\Http\Request;

class SettingPriceController extends Controller
{
    public function index()
    {
        $title = "Setting Price Verificator";
        $divisions = Division::orderBy('name')->get();
        $budgetCode = BudgetCode::all();
        $priceVerificators = PriceVerification::with('codes', 'users.jobPosition')->orderBy('verificator')->get();
        $jobPositions = JobPosition::orderBy('job_position_name')->get();
        return view('pages.settings.settingPrice', compact('divisions', 'budgetCode', 'priceVerificators', 'jobPositions', 'title'));
    }

    public function storeVerificator(Request $request)
    {
        PriceVerification::create([
            'verificator' => $request->verificator,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true]);
    }

    public function assignCode(Request $request)
    {
        PriceVerificationCode::create([
            'price_verification_id' => $request->price_verification_id,
            'remarks' => $request->remarks,
            'inchargecode' => $request->inchargecode,
        ]);

        return response()->json(['success' => true]);
    }

    public function assignUser(Request $request)
    {
        PriceVerificationUser::create([
            'price_verification_id' => $request->price_verification_id,
            'job_position_id' => $request->job_position_id,
        ]);

        return response()->json(['success' => true]);
    }
}
