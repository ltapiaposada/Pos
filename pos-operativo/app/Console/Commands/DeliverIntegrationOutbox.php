<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DeliverIntegrationOutbox extends Command
{
    protected $signature = 'integration:deliver-outbox {--limit=100}';

    protected $description = 'Entrega eventos pendientes del POS a e-commerce.';

    public function handle(): int
    {
        $events = DB::table('integration_outbox_events')
            ->whereNull('published_at')
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        foreach ($events as $event) {
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

        $this->info("Eventos procesados: {$events->count()}");

        return self::SUCCESS;
    }
}
