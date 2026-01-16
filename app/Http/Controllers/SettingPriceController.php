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
        
        // Get unique budget codes that have inchargeCode value
        $budgetCodes = BudgetCode::whereNotNull('inchargecode')
            ->where('inchargecode', '!=', '')
            ->select('inchargecode', 'name', 'remarks')
            ->orderBy('inchargecode')
            ->get()
            ->unique('inchargecode')  // Ensure unique by inchargecode
            ->values();  // Reset array keys
            
        $priceVerificators = PriceVerification::with('codes', 'users.jobPosition')->orderBy('verificator')->get();
        $jobPositions = JobPosition::orderBy('job_position_name')->get();
        return view('pages.settings.settingPrice', compact('divisions', 'budgetCodes', 'priceVerificators', 'jobPositions', 'title'));
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
        // Check for duplicate
        $exists = PriceVerificationCode::where('price_verification_id', $request->price_verification_id)
            ->where('remarks', $request->remarks)
            ->where('inchargecode', $request->inchargecode)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This code already assigned to this verificator with same remarks'
            ], 422);
        }

        PriceVerificationCode::create([
            'price_verification_id' => $request->price_verification_id,
            'remarks' => $request->remarks,
            'inchargecode' => $request->inchargecode,
        ]);

        return response()->json(['success' => true, 'message' => 'Code assigned successfully']);
    }

    public function updateCode(Request $request, $id)
    {
        $code = PriceVerificationCode::findOrFail($id);

        // Check for duplicate (excluding current record)
        $exists = PriceVerificationCode::where('price_verification_id', $request->price_verification_id)
            ->where('remarks', $request->remarks)
            ->where('inchargecode', $request->inchargecode)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This code already assigned to this verificator with same remarks'
            ], 422);
        }

        $code->update([
            'price_verification_id' => $request->price_verification_id,
            'remarks' => $request->remarks,
            'inchargecode' => $request->inchargecode,
        ]);

        return response()->json(['success' => true, 'message' => 'Code updated successfully']);
    }

    public function deleteCode($id)
    {
        $code = PriceVerificationCode::findOrFail($id);
        $code->delete();

        return response()->json(['success' => true, 'message' => 'Code removed successfully']);
    }

    public function assignUser(Request $request)
    {
        // Check for duplicate
        $exists = PriceVerificationUser::where('price_verification_id', $request->price_verification_id)
            ->where('job_position_id', $request->job_position_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This user already assigned to this verificator'
            ], 422);
        }

        PriceVerificationUser::create([
            'price_verification_id' => $request->price_verification_id,
            'job_position_id' => $request->job_position_id,
        ]);

        return response()->json(['success' => true, 'message' => 'User assigned successfully']);
    }

    public function deleteUser($id)
    {
        $user = PriceVerificationUser::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User removed successfully']);
    }

    public function updateVerificator(Request $request, $id)
    {
        $verificator = PriceVerification::findOrFail($id);
        $verificator->update([
            'verificator' => $request->verificator,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Verificator updated successfully']);
    }

    public function deleteVerificator($id)
    {
        $verificator = PriceVerification::findOrFail($id);
        
        // Delete related codes and users
        $verificator->codes()->delete();
        $verificator->users()->delete();
        $verificator->delete();

        return response()->json(['success' => true, 'message' => 'Verificator deleted successfully']);
    }
}
