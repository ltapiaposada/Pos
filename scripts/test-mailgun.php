<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$to = getenv('TEST_MAIL_TO') ?: config('mail.from.address');
$mailer = (string) config('mail.default');
$from = (string) config('mail.from.address');

echo 'mailer=' . $mailer . PHP_EOL;
echo 'from=' . $from . PHP_EOL;
echo 'to=' . $to . PHP_EOL;
echo 'mailgun_domain=' . (string) config('services.mailgun.domain') . PHP_EOL;
echo 'mailgun_endpoint=' . (string) config('services.mailgun.endpoint') . PHP_EOL;

try {
    \Illuminate\Support\Facades\Mail::raw(
        'Prueba de Mailgun desde Laravel. Fecha: ' . date('Y-m-d H:i:s'),
        static function (\Illuminate\Mail\Message $message) use ($to): void {
            $message->to($to)->subject('Prueba Mailgun Laravel');
        }
    );

    echo 'STATUS=OK' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'STATUS=ERROR' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
