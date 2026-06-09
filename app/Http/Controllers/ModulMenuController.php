<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\StoreModulMenuRequest;
use App\Http\Requests\UpdateModulMenuRequest;
use App\Services\UserSettingsService\DTOs\ModulMenuData;
use App\Services\UserSettingsService\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ModulMenuController extends Controller
{
    public function __construct(
        private readonly UserSettingsService $userSettingsService,
    ) {
    }

    public function store(StoreModulMenuRequest $request): JsonResponse
    {
        try {
            $modulMenu = $this->userSettingsService->createModulMenu(
                ModulMenuData::fromArray($request->validated())
            );

            return response()->json([
                'success' => true,
                'message' => 'Modul berhasil ditambahkan.',
                'data' => $modulMenu,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('ModulMenuController@store failed', ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function update(UpdateModulMenuRequest $request, int $id): JsonResponse
    {
        try {
            $modulMenu = $this->userSettingsService->updateModulMenu(
                $id,
                ModulMenuData::fromArray($request->validated())
            );

            return response()->json([
                'success' => true,
                'message' => 'Modul berhasil diperbarui.',
                'data' => $modulMenu,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('ModulMenuController@update failed', ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userSettingsService->deleteModulMenu($id);

            return response()->json([
                'success' => true,
                'message' => 'Modul berhasil dihapus.',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('ModulMenuController@destroy failed', ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }
}
