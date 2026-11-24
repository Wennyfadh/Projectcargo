<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExternalApiService;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ExternalApiService $externalApiService,
        protected TrackingService $trackingService
    ) {
    }

    public function index(Request $request): View
    {
        $query = Shipment::query()->visibleTo($request->user());
        $shipments = (clone $query)
            ->with(['carrier', 'customer', 'origin', 'destination', 'currentLocation', 'exceptions'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $dashboard = [
            'total_shipments' => (clone $query)->count(),
            'in_transit' => (clone $query)->where('status', 'IN_TRANSIT')->count(),
            'delivered' => (clone $query)->where('status', 'DELIVERED')->count(),
            'delayed' => (clone $query)->whereIn('delay_status', ['LOW_DELAY', 'MEDIUM_DELAY', 'HIGH_DELAY'])->count(),
            'open_exceptions' => (clone $query)->whereHas('exceptions', fn ($exceptions) => $exceptions->whereIn('status', ['OPEN', 'INVESTIGATING']))->count(),
            'on_time_rate' => (clone $query)->count() > 0
                ? round(((clone $query)->where('delay_status', 'ON_TIME')->count() / (clone $query)->count()) * 100, 2)
                : 0,
            'exception_rate' => (clone $query)->count() > 0
                ? round(((clone $query)->has('exceptions')->count() / (clone $query)->count()) * 100, 2)
                : 0,
        ];

        $statusBreakdown = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->get()->map(fn ($item) => [
            'label' => $item->status,
            'value' => (int) $item->total,
        ]);

        $carrierBreakdown = (clone $query)->selectRaw('carrier_id, COUNT(*) as total')->groupBy('carrier_id')->with('carrier')->get()->map(fn ($item) => [
            'label' => $item->carrier?->name ?? 'Unknown Carrier',
            'value' => (int) $item->total,
        ]);

        $weatherLocation = $shipments->first()?->origin ?? Location::query()
            ->where('facility_type', 'PORT')
            ->first();
        $weather = $weatherLocation
            ? $this->externalApiService->fetchMarineWeather($weatherLocation)
            : [];

        $news = $this->externalApiService->fetchGlobalNews();

        $routePoints = (clone $query)->with(['origin', 'destination', 'currentLocation', 'carrier'])->get()->map(function ($shipment) {
            $destLabel = $shipment->destination?->name ?? $shipment->destination?->city ?? 'Destination Port';
            if ($shipment->destination?->country_code) {
                $destLabel .= ', '.$shipment->destination->country_code;
            }

            $mode = $shipment->transport_mode ?? 'SEA';
            $calloutPrefix = match ($mode) {
                'AIR' => 'Dalam penerbangan ke',
                'ROAD' => 'Dalam perjalanan darat ke',
                default => 'Dalam perjalanan ke',
            };
            $accentColor = match ($mode) {
                'AIR' => '#0284c7', // Sky Blue
                'ROAD' => '#059669', // Emerald Green
                default => '#00a884', // Vibrant Teal
            };
            $modeIcon = match ($mode) {
                'AIR' => '✈️',
                'ROAD' => '🚛',
                default => '🚢',
            };

            return [
                'id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'transport_mode' => $mode,
                'transport_mode_label' => $shipment->transport_mode_label,
                'transport_mode_icon' => $modeIcon,
                'callout_prefix' => $calloutPrefix,
                'accent_color' => $accentColor,
                'status' => $shipment->status,
                'delay_status' => $shipment->delay_status,
                'vehicle_name' => $shipment->vehicle_identifier ?? $shipment->vessel_name ?? 'Fleet Vehicle',
                'vessel_name' => $shipment->vessel_name ?? 'Ocean Vessel',
                'carrier' => $shipment->carrier?->name ?? 'Carrier',
                'latitude' => $shipment->currentLocation?->latitude ?? $shipment->origin?->latitude,
                'longitude' => $shipment->currentLocation?->longitude ?? $shipment->origin?->longitude,
                'name' => $shipment->currentLocation?->name ?? $shipment->origin?->name,
                'origin' => $shipment->origin?->name,
                'origin_city' => $shipment->origin?->city,
                'origin_latitude' => $shipment->origin?->latitude,
                'origin_longitude' => $shipment->origin?->longitude,
                'destination' => $shipment->destination?->name,
                'destination_city' => $shipment->destination?->city,
                'destination_label' => $destLabel,
                'destination_latitude' => $shipment->destination?->latitude,
                'destination_longitude' => $shipment->destination?->longitude,
                'tracking_url' => route('tracking.show', $shipment),
            ];
        })->filter(fn ($point) => ! empty($point['latitude']) && ! empty($point['longitude']))->values();

        return view('dashboard', compact('dashboard', 'shipments', 'statusBreakdown', 'carrierBreakdown', 'weather', 'news', 'routePoints'));
    }

    public function shipments(Request $request): View
    {
        $query = Shipment::query()->visibleTo($request->user())->with(['customer', 'carrier', 'origin', 'destination']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('shipment_number', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bill_of_lading_number', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        $shipments = $query->latest('updated_at')->paginate(10)->withQueryString();
        $carriers = Carrier::query()->orderBy('name')->get();

        return view('shipments.index', compact('shipments', 'carriers'));
    }

    public function tracking(Request $request): View
    {
        $query = Shipment::query()
            ->visibleTo($request->user())
            ->with(['customer', 'carrier', 'origin', 'destination', 'currentLocation', 'latestTrackingEvent'])
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED']);

        if ($request->filled('mode') && $request->input('mode') !== 'ALL') {
            $query->where('transport_mode', strtoupper($request->input('mode')));
        }

        $shipments = $query->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('tracking.index', compact('shipments'));
    }

    public function trackingShow(Request $request, Shipment $shipment): View
    {
        $this->authorize('view', $shipment);
        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'currentLocation',
            'trackingEvents.location',
            'trackingEvents.creator',
            'items',
            'exceptions',
        ]);

        $telemetry = $this->trackingService->getRouteTelemetry($shipment);
        $currentLat = $telemetry['current_position']['latitude'];
        $currentLng = $telemetry['current_position']['longitude'];

        $weather = $this->externalApiService->fetchWeatherForCoordinates($currentLat, $currentLng);

        return view('tracking.show', compact('shipment', 'telemetry', 'weather'));
    }

    public function trackingData(Request $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);
        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'currentLocation',
            'trackingEvents.location',
        ]);

        $telemetry = $this->trackingService->getRouteTelemetry($shipment);
        $weather = $this->externalApiService->fetchWeatherForCoordinates(
            $telemetry['current_position']['latitude'],
            $telemetry['current_position']['longitude']
        );

        return response()->json([
            'shipment' => [
                'id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'container_number' => $shipment->container_number,
                'seal_number' => $shipment->seal_number,
                'status' => $shipment->status,
                'delay_status' => $shipment->delay_status,
                'vessel_name' => $shipment->vessel_name,
                'voyage_number' => $shipment->voyage_number,
                'carrier' => $shipment->carrier?->name,
                'carrier_scac' => $shipment->carrier?->scac,
                'estimated_arrival_at' => $shipment->estimated_arrival_at?->format('d M Y, H:i'),
                'actual_departure_at' => $shipment->actual_departure_at?->format('d M Y, H:i'),
            ],
            'telemetry' => $telemetry,
            'weather' => $weather,
            'synced_at' => now()->format('H:i:s T'),
        ]);
    }

    public function exceptions(Request $request): View
    {
        $exceptions = ShipmentException::query()
            ->whereHas('shipment', fn ($query) => $query->visibleTo($request->user()))
            ->with(['shipment.customer', 'shipment.carrier'])
            ->latest('detected_at')
            ->paginate(10)
            ->withQueryString();

        return view('exceptions.index', compact('exceptions'));
    }

    public function carriers(Request $request): View
    {
        // Allow Admin, Operator, and Customer to view global carrier directory

        $carriers = Carrier::query()->withCount('shipments')->orderBy('name')->paginate(10);

        return view('carriers.index', compact('carriers'));
    }

    public function reports(Request $request): View
    {
        $query = Shipment::query()->visibleTo($request->user());
        $statusBreakdown = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->orderBy('status')->get();
        $carrierBreakdown = (clone $query)->selectRaw('carrier_id, COUNT(*) as total')->with('carrier')->groupBy('carrier_id')->get();

        return view('reports.index', compact('statusBreakdown', 'carrierBreakdown'));
    }

    public function marketTelemetry(): JsonResponse
    {
        return response()->json($this->externalApiService->fetchMarketTelemetry());
    }
}
