<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    public function uploadImage(UploadedFile $file, ?string $urlOverride = null): string
    {
        $config = $this->parseCloudinaryUrl($urlOverride);
        $cloudName = $config['cloud_name'];
        $apiKey = $config['api_key'];
        $apiSecret = $config['api_secret'];
        $folder = config('cloudinary.folder', 'pos');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new RuntimeException('Cloudinary not configured.');
        }

        $timestamp = time();
        $signature = $this->signature([
            'folder' => $folder,
            'timestamp' => $timestamp,
        ], $apiSecret);

        $http = Http::asMultipart();
        if (!config('cloudinary.verify_ssl', true)) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            [
                'name' => 'file',
                'contents' => fopen($file->getRealPath(), 'r'),
                'filename' => $file->getClientOriginalName(),
            ],
            ['name' => 'api_key', 'contents' => $apiKey],
            ['name' => 'timestamp', 'contents' => (string) $timestamp],
            ['name' => 'folder', 'contents' => $folder],
            ['name' => 'signature', 'contents' => $signature],
        ]);

        if (!$response->successful()) {
            $body = $response->body();
            $snippet = mb_substr($body, 0, 500);
            throw new RuntimeException('Cloudinary upload failed. Status: ' . $response->status() . ' Body: ' . $snippet);
        }

        $payload = $response->json();
        return $payload['secure_url'] ?? $payload['url'] ?? '';
    }

    private function signature(array $params, string $apiSecret): string
    {
        ksort($params);
        $toSign = urldecode(http_build_query($params));
        return sha1($toSign . $apiSecret);
    }

    private function parseCloudinaryUrl(?string $override): array
    {
        $source = 'config';
        $url = trim((string) ($override ?: (
            config('cloudinary.url')
            ?: getenv('CLOUDINARY_URL')
            ?: ($_ENV['CLOUDINARY_URL'] ?? null)
            ?: ($_SERVER['CLOUDINARY_URL'] ?? null)
        )));

        if (!$url) {
            $source = 'file';
            $envPath = base_path('.env');
            if (is_readable($envPath)) {
                $raw = file_get_contents($envPath);
                if (is_string($raw) && preg_match('/^CLOUDINARY_URL=(.+)$/m', $raw, $matches)) {
                    $url = trim($matches[1]);
                    $url = trim($url, "\"' ");
                }
            }
        }

        if (!$url) {
            logger()->error('Cloudinary URL empty in config/env.');
            return [
                'cloud_name' => null,
                'api_key' => null,
                'api_secret' => null,
            ];
        }

        $masked = preg_replace('/cloudinary:\\/\\/([^:]+):([^@]+)@/', 'cloudinary://$1:***@', $url);
        logger()->info('Cloudinary URL loaded from ' . $source . ': ' . $masked);

        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? null) !== 'cloudinary') {
            throw new RuntimeException('CLOUDINARY_URL invalid.');
        }
        $cloudName = $parts['host'] ?? '';
        if (!$cloudName) {
            $cloudName = ltrim($parts['path'] ?? '', '/');
        }
        $apiKey = $parts['user'] ?? null;
        $apiSecret = $parts['pass'] ?? null;

        logger()->info('Cloudinary parse result', [
            'cloud_name' => $cloudName,
            'api_key_len' => $apiKey ? strlen($apiKey) : 0,
            'api_secret_len' => $apiSecret ? strlen($apiSecret) : 0,
        ]);

        return [
            'cloud_name' => $cloudName ?: null,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
        ];
    }
}
