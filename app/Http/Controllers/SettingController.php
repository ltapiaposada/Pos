<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogoUploadRequest;
use App\Http\Requests\QrUploadRequest;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use App\Models\Tax;
use App\Services\ImageStorageService;

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

    public function uploadLogo(LogoUploadRequest $request, ImageStorageService $imageStorage)
    {
        return $this->uploadImageResponse(
            $request->file('logo'),
            $imageStorage,
            'No se pudo subir el logo.'
        );
    }

    public function uploadQr(QrUploadRequest $request, ImageStorageService $imageStorage)
    {
        return $this->uploadImageResponse(
            $request->file('qr'),
            $imageStorage,
            'No se pudo subir el QR.'
        );
    }

    public function update(SettingRequest $request, ImageStorageService $imageStorage)
    {
        $setting = Setting::query()->firstOrCreate(
            ['key' => 'business'],
            ['value' => []]
        );

        logger()->info('Settings update received.', [
            'has_logo' => $request->hasFile('logo'),
            'has_payment_qr' => $request->hasFile('payment_qr'),
            'logo_name' => $request->file('logo')?->getClientOriginalName(),
            'logo_size' => $request->file('logo')?->getSize(),
            'qr_name' => $request->file('payment_qr')?->getClientOriginalName(),
            'qr_size' => $request->file('payment_qr')?->getSize(),
        ]);

        $payload = $request->safe()->except(['logo', 'payment_qr']);
        $payload['allow_negative_stock'] = (bool) ($payload['allow_negative_stock'] ?? false);
        $currentBusiness = is_array($setting->value) ? $setting->value : [];

        if ($request->hasFile('logo')) {
            try {
                $logoUrl = $imageStorage->uploadImage($request->file('logo'));
                if (! $logoUrl) {
                    return back()->withErrors(['logo' => 'No se pudo subir el logo.'])->withInput();
                }
                logger()->info('Logo uploaded successfully during settings update.', [
                    'logo_url' => $logoUrl,
                ]);
                $payload['logo_url'] = $logoUrl;
                $previousLogoUrl = $currentBusiness['logo_url'] ?? null;
                if ($previousLogoUrl && $previousLogoUrl !== $logoUrl) {
                    $imageStorage->deleteImageByUrl($previousLogoUrl);
                }
            } catch (\Throwable $e) {
                report($e);
                logger()->error('Logo upload failed during settings update.', [
                    'message' => $e->getMessage(),
                    'logo_name' => $request->file('logo')?->getClientOriginalName(),
                    'logo_size' => $request->file('logo')?->getSize(),
                ]);
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'No se pudo subir el logo. Verifica la configuracion de Cloudflare R2.';

                return back()->withErrors(['logo' => $message])->withInput();
            }
        } else {
            $payload['logo_url'] = $payload['logo_url'] ?? ($currentBusiness['logo_url'] ?? null);
        }

        if ($request->hasFile('payment_qr')) {
            try {
                $qrUrl = $imageStorage->uploadImage($request->file('payment_qr'));
                if (! $qrUrl) {
                    return back()->withErrors(['payment_qr' => 'No se pudo subir el QR.'])->withInput();
                }
                logger()->info('QR uploaded successfully during settings update.', [
                    'qr_url' => $qrUrl,
                ]);
                $payload['payment_qr_url'] = $qrUrl;
                $previousQrUrl = $currentBusiness['payment_qr_url'] ?? null;
                if ($previousQrUrl && $previousQrUrl !== $qrUrl) {
                    $imageStorage->deleteImageByUrl($previousQrUrl);
                }
            } catch (\Throwable $e) {
                report($e);
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'No se pudo subir el QR. Verifica la configuracion de Cloudflare R2.';

                return back()->withErrors(['payment_qr' => $message])->withInput();
            }
        } else {
            $payload['payment_qr_url'] = $payload['payment_qr_url'] ?? ($currentBusiness['payment_qr_url'] ?? null);
        }

        $setting->update(['value' => $payload]);
        Setting::forgetValue('business');

        return redirect()->route('settings.edit')->with('status', 'Configuracion actualizada.');
    }

    private function uploadImageResponse($file, ImageStorageService $imageStorage, string $fallbackMessage)
    {
        if (! $file || ! $file->isValid()) {
            $message = $file ? $file->getErrorMessage() : 'Selecciona un archivo valido.';
            if ($file && $file->getError() === UPLOAD_ERR_INI_SIZE) {
                $message = 'Solo se permiten archivos de hasta 2MB.';
            }

            return response()->json(['message' => $message], 422);
        }

        try {
            $imageUrl = $imageStorage->uploadImage($file);
            if (! $imageUrl) {
                logger()->error('Logo/QR upload returned an empty URL.', [
                    'field' => $file?->getClientOriginalName(),
                    'fallback_message' => $fallbackMessage,
                ]);

                return response()->json(['message' => $fallbackMessage], 422);
            }

            return response()->json(['url' => $imageUrl]);
        } catch (\Throwable $e) {
            report($e);

             logger()->error('Upload endpoint failed.', [
                 'message' => $e->getMessage(),
                 'file' => $file?->getClientOriginalName(),
                 'mime_type' => $file?->getMimeType(),
                 'size' => $file?->getSize(),
             ]);

            return response()->json([
                'message' => config('app.debug') ? $e->getMessage() : $fallbackMessage,
            ], 422);
        }
    }
}
