<?php

namespace App\Http\Controllers;

use App\Services\PipIntegrationService\PipIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Proxy controller that forwards requests to the PIP external API via PipIntegrationService.
 * All endpoints are authenticated – never expose PIP credentials to the frontend.
 */
class PipController extends Controller
{
    public function __construct(
        private readonly PipIntegrationService $pipService,
    ) {}

    public function getJenisKas(Request $request): JsonResponse
    {
        return response()->json(
            $this->pipService->getJenisKas($request->input('keyword'))->toArray()
        );
    }

    public function getJenisTransaksi(Request $request): JsonResponse
    {
        return response()->json(
            $this->pipService->getJenisTransaksi($request->input('keyword'))->toArray()
        );
    }

    public function getCostCenter(Request $request): JsonResponse
    {
        return response()->json(
            $this->pipService->getCostCenter($request->input('keyword'))->toArray()
        );
    }

    public function getVendor(Request $request): JsonResponse
    {
        return response()->json(
            $this->pipService->getVendor($request->input('keyword'))->toArray()
        );
    }

    public function getPpn(Request $request): JsonResponse
    {
        return response()->json(
            $this->pipService->getPpn($request->input('keyword'))->toArray()
        );
    }

    public function getTax(Request $request): JsonResponse
    {
        return response()->json(
            $this->pipService->getTax($request->input('keyword'))->toArray()
        );
    }

    public function getPph(): JsonResponse
    {
        return response()->json($this->pipService->getPph()->toArray());
    }
}
