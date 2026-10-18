<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EcommerceEventPublisher
{
    public function publish(string $eventUuid): void
    {
        $event = DB::table('integration_outbox_events')->where('event_uuid', $eventUuid)->first();

        if (! $event || $event->published_at) {
            return;
        }

        try {
            Http::baseUrl(config('integration.ecommerce_url'))
                ->acceptJson()
                ->asJson()
                ->withHeader('X-Internal-Token', config('integration.internal_token'))
                ->timeout(10)
                ->post('/api/internal/v1/events/pos', [
                    'event_uuid' => $event->event_uuid,
                    'event_type' => $event->event_type,
                    'payload' => json_decode($event->payload, true, flags: JSON_THROW_ON_ERROR),
                ])
                ->throw();

            DB::table('integration_outbox_events')->where('id', $event->id)->update([
                'published_at' => now(),
                'attempts' => $event->attempts + 1,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            DB::table('integration_outbox_events')->where('id', $event->id)->update([
                'attempts' => $event->attempts + 1,
                'updated_at' => now(),
            ]);
            report($exception);
        }
    }
}
