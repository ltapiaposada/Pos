<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\CheckoutOrder;
use App\Models\InboundEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosEventController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_uuid' => ['required', 'uuid'],
            'event_type' => ['required', 'string', 'max:100'],
            'payload' => ['required', 'array'],
        ]);

        $event = InboundEvent::firstOrCreate(
            ['event_uuid' => $data['event_uuid']],
            ['event_type' => $data['event_type'], 'payload' => $data['payload']]
        );

        if ($event->wasRecentlyCreated && $data['event_type'] === 'pos.order.status_changed') {
            CheckoutOrder::where('external_order_uuid', $data['payload']['external_order_uuid'] ?? null)
                ->update(['status' => $data['payload']['status'] ?? 'pending_pos']);
            $event->update(['processed_at' => now()]);
        }

        return response()->json(['event_uuid' => $event->event_uuid, 'duplicate' => ! $event->wasRecentlyCreated]);
    }
}
