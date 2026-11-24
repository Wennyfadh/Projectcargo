<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ExceptionService $exceptionService)
    {
    }

    public function index(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        return response()->json($shipment->exceptions()->with('shipment')->get());
    }

    public function store(Request $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        $exception = $this->exceptionService->trigger($shipment, $request->all(), $request->user());

        return response()->json($exception, 201);
    }

    public function update(Request $request, ShipmentException $exception): JsonResponse
    {
        $this->authorize('update', $exception);

        $exception = $this->exceptionService->resolve($exception, $request->all(), $request->user());

        return response()->json($exception);
    }
}
