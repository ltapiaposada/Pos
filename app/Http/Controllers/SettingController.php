<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogoUploadRequest;
use App\Http\Requests\QrUploadRequest;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use App\Models\Tax;
use App\Services\CloudinaryService;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::query()->firstOrCreate(
            ['key' => 'business'],
            ['value' => []]
        );

        $taxes = Tax::query()->where('is_active', true)->orderBy('name')->get();

        return view('settings.edit', [
            'business' => $setting->value ?? [],
            'taxes' => $taxes,
        ]);
    }

    public function uploadLogo(LogoUploadRequest $request, CloudinaryService $cloudinary)
    {
        return $this->uploadImageResponse(
            $request->file('logo'),
            $cloudinary,
            'No se pudo subir el logo.'
        );
    }

    public function uploadQr(QrUploadRequest $request, CloudinaryService $cloudinary)
    {
        return $this->uploadImageResponse(
            $request->file('qr'),
            $cloudinary,
            'No se pudo subir el QR.'
        );
    }

    public function update(SettingRequest $request, CloudinaryService $cloudinary)
    {
        $setting = Setting::query()->firstOrCreate(
            ['key' => 'business'],
            ['value' => []]
        );

        $payload = $request->safe()->except(['logo', 'payment_qr']);
        $payload['allow_negative_stock'] = (bool) ($payload['allow_negative_stock'] ?? false);

        if ($request->hasFile('logo')) {
            try {
                $logoUrl = $cloudinary->uploadImage($request->file('logo'));
                if (! $logoUrl) {
                    return back()->withErrors(['logo' => 'No se pudo subir el logo.'])->withInput();
                }
                $payload['logo_url'] = $logoUrl;
            } catch (\Throwable $e) {
                report($e);
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'No se pudo subir el logo. Verifica CLOUDINARY_URL.';

                return back()->withErrors(['logo' => $message])->withInput();
            }
        } else {
            $payload['logo_url'] = $payload['logo_url'] ?? ($setting->value['logo_url'] ?? null);
        }

        if ($request->hasFile('payment_qr')) {
            try {
                $qrUrl = $cloudinary->uploadImage($request->file('payment_qr'));
                if (! $qrUrl) {
                    return back()->withErrors(['payment_qr' => 'No se pudo subir el QR.'])->withInput();
                }
                $payload['payment_qr_url'] = $qrUrl;
            } catch (\Throwable $e) {
                report($e);
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'No se pudo subir el QR. Verifica CLOUDINARY_URL.';

                return back()->withErrors(['payment_qr' => $message])->withInput();
            }
        } else {
            $payload['payment_qr_url'] = $payload['payment_qr_url'] ?? ($setting->value['payment_qr_url'] ?? null);
        }

        $setting->update(['value' => $payload]);
        Setting::forgetValue('business');

        return redirect()->route('settings.edit')->with('status', 'Configuracion actualizada.');
    }

    private function uploadImageResponse($file, CloudinaryService $cloudinary, string $fallbackMessage)
    {
        if (! $file || ! $file->isValid()) {
            $message = $file ? $file->getErrorMessage() : 'Selecciona un archivo valido.';

            return response()->json(['message' => $message], 422);
        }

        try {
            $urlOverride = $this->readCloudinaryUrlFromEnv();
            $imageUrl = $cloudinary->uploadImage($file, $urlOverride);
            if (! $imageUrl) {
                return response()->json(['message' => $fallbackMessage], 422);
            }

            return response()->json(['url' => $imageUrl]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => config('app.debug') ? $e->getMessage() : $fallbackMessage,
            ], 422);
        }
    }

    private function readCloudinaryUrlFromEnv(): ?string
    {
        $url = env('CLOUDINARY_URL') ?: getenv('CLOUDINARY_URL');
        if ($url) {
            return $url;
        }

        $envPath = base_path('.env');
        if (! is_readable($envPath)) {
            return null;
        }

        $raw = file_get_contents($envPath);
        if (is_string($raw) && preg_match('/^CLOUDINARY_URL=(.+)$/m', $raw, $matches)) {
            $value = trim($matches[1]);

            return trim($value, "\"' ");
        }

        return null;
    }
}

