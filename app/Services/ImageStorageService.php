<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageStorageService
{
    public function uploadImage(UploadedFile $file): string
    {
        $diskName = config('filesystems.image_disk', 'r2');
        $diskConfig = config("filesystems.disks.{$diskName}");

        if (!is_array($diskConfig) || empty($diskConfig['driver'])) {
            throw new RuntimeException("Image disk [{$diskName}] not configured.");
        }

        $prefix = trim((string) config('filesystems.image_prefix', 'pos'), '/');
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid()->toString() . '.' . strtolower($extension);
        $path = ltrim($prefix . '/' . now()->format('Y/m') . '/' . $filename, '/');
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $stored = Storage::disk($diskName)->put($path, fopen($file->getRealPath(), 'r'), [
            'visibility' => 'public',
            'ContentType' => $mimeType,
        ]);

        if (!$stored) {
            logger()->error('Image upload returned false.', [
                'disk' => $diskName,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size' => $file->getSize(),
            ]);

            throw new RuntimeException("Image upload to [{$diskName}] failed.");
        }

        $url = Storage::disk($diskName)->url($path);

        if (!is_string($url) || trim($url) === '') {
            logger()->error('Image upload succeeded but URL generation failed.', [
                'disk' => $diskName,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);

            throw new RuntimeException("Image uploaded to [{$diskName}] but URL generation failed.");
        }

        return $url;
    }

    public function deleteImageByUrl(?string $url): void
    {
        $url = trim((string) $url);
        if ($url === '') {
            return;
        }

        $diskName = config('filesystems.image_disk', 'r2');
        $baseUrl = rtrim((string) Storage::disk($diskName)->url(''), '/');

        if ($baseUrl === '' || !str_starts_with($url, $baseUrl . '/')) {
            return;
        }

        $path = ltrim(substr($url, strlen($baseUrl)), '/');
        if ($path === '') {
            return;
        }

        try {
            Storage::disk($diskName)->delete($path);
        } catch (\Throwable $e) {
            logger()->warning('Previous image could not be deleted.', [
                'disk' => $diskName,
                'url' => $url,
                'path' => $path,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
