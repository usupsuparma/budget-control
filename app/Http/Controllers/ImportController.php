<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvRequest;
use App\Services\WorkplanImportService\WorkplanImportService;
use Exception;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function __construct(
        protected WorkplanImportService $importService
    ) {}

    public function showImportForm()
    {
        return view('import-workplan.index');
    }

    public function import(ImportCsvRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->importService->importWorkplanBudget($data['file']);
            
            return response()->json([
                'success' => true,
                'message' => 'File CSV berhasil diproses.',
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage(), ['exception' => $e]);
            // Format fallback according to standard
            $statusCode = 500;
            if (str_contains(get_class($e), 'Exception\\') || str_contains($e->getMessage(), 'Business') || $e->getCode() == 422) {
                $statusCode = 422;
            }
            return response()->json([
                'success' => false,
                'message' => $statusCode === 500 ? 'Internal Server Error' : $e->getMessage(),
            ], $statusCode);
        }
    }
}
