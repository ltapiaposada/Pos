<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$domain = (string) config('services.mailgun.domain');
$secret = (string) config('services.mailgun.secret');
$endpoint = (string) config('services.mailgun.endpoint', 'api.mailgun.net');
$recipient = $argv[1] ?? '';
$insecure = in_array('--insecure', $argv, true);

if ($domain === '' || $secret === '') {
    fwrite(STDERR, "Falta MAILGUN_DOMAIN o MAILGUN_SECRET en .env\n");
    exit(1);
}

$baseUrl = sprintf('https://%s/v3/%s/events', $endpoint, $domain);
$query = [
    'limit' => 15,
];

if ($recipient !== '') {
    $query['recipient'] = $recipient;
}

try {
    $response = Illuminate\Support\Facades\Http::withBasicAuth('api', $secret)
        ->acceptJson()
        ->withOptions([
            'verify' => ! $insecure,
        ])
        ->timeout(20)
        ->get($baseUrl, $query);
} catch (Throwable $e) {
    fwrite(STDERR, "Error consultando Mailgun: {$e->getMessage()}\n");
    exit(1);
}

echo 'HTTP=' . $response->status() . PHP_EOL;
if (! $response->successful()) {
    echo $response->body() . PHP_EOL;
    exit(1);
}

$items = $response->json('items') ?? [];
if (! is_array($items) || count($items) === 0) {
    echo "SIN_EVENTOS\n";
    exit(0);
}

foreach ($items as $item) {
    $event = (string) ($item['event'] ?? '');
    $time = isset($item['timestamp']) ? date('Y-m-d H:i:s', (int) $item['timestamp']) : '';
    $to = (string) ($item['recipient'] ?? '');
    $subject = (string) ($item['message']['headers']['subject'] ?? '');
    $reason = (string) ($item['reason'] ?? '');
    $severity = (string) ($item['severity'] ?? '');

    echo "time={$time} event={$event} to={$to} subject={$subject}";
    if ($reason !== '') {
        echo " reason={$reason}";
    }
    if ($severity !== '') {
        echo " severity={$severity}";
    }
    echo PHP_EOL;
}
