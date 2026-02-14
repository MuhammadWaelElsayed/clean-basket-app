<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\WorkingHoursService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AgentVendorTimeSlotsController extends Controller
{
    public function pickup($vendorId, Request $req, WorkingHoursService $svc)
    {
        $validated = $req->validate([
            'date'         => ['nullable', 'date'], // YYYY-MM-DD
            'min_count'    => ['nullable', 'integer', 'min:1', 'max:50'],
            'days_horizon' => ['nullable', 'integer', 'min:1', 'max:14'],
            'slot_minutes' => ['nullable', 'integer', 'in:30,60,90,120'],
        ]);

        $slots = $svc->listPickupSlots(
            vendorId: (int)$vendorId,
            dateYmd: $validated['date'] ?? null,
            daysHorizon: $validated['days_horizon'] ?? 2,
            minCount: $validated['min_count'] ?? 6,
            slotMinutes: $validated['slot_minutes'] ?? 60
        );

        return response()->json([
            'status' => true,
            'data'   => $slots,
        ]);
    }

    public function delivery($vendorId, Request $req, WorkingHoursService $svc)
    {
        $validated = $req->validate([
            'pickup_at'    => ['required', 'date'], // ISO أو "YYYY-MM-DD HH:mm"
            'gap_hours'    => ['nullable', 'integer', 'min:1', 'max:72'],
            'min_count'    => ['nullable', 'integer', 'min:1', 'max:50'],
            'days_horizon' => ['nullable', 'integer', 'min:1', 'max:14'],
            'slot_minutes' => ['nullable', 'integer', 'in:30,60,90,120'],
        ]);

        $pickupAt = Carbon::parse($validated['pickup_at']);

        $slots = $svc->listDeliverySlots(
            vendorId: (int)$vendorId,
            pickupAt: $pickupAt,
            gapHours: $validated['gap_hours'] ?? 20,          // شرطك: +20 ساعة
            daysHorizon: $validated['days_horizon'] ?? 5,
            minCount: $validated['min_count'] ?? 6,
            slotMinutes: $validated['slot_minutes'] ?? 60
        );

        return response()->json([
            'status' => true,
            'data'   => $slots,
        ]);
    }
}
