<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanyLogoStorage
{
    public function upload(UploadedFile $file, int $companyId): array
    {
        $parameters = $this->signed([
            'public_id' => 'itviec/companies/'.$companyId.'/'.Str::uuid(),
            'overwrite' => 'false',
            'timestamp' => (string) time(),
        ]);

        try {
            $response = Http::connectTimeout(5)->timeout(30)
                ->attach('file', $file->getContent(), 'logo.'.$file->extension())
                ->post($this->endpoint('upload'), $parameters);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['logo' => 'Không kết nối được Cloudinary. Vui lòng thử lại.']);
        }

        if (! $response->successful() || ! $response->json('secure_url') || ! $response->json('public_id')) {
            Log::warning('Cloudinary logo upload failed.', ['status' => $response->status()]);
            throw ValidationException::withMessages(['logo' => 'Không tải được logo lên Cloudinary. Vui lòng kiểm tra cấu hình hoặc thử lại.']);
        }

        return ['public_id' => $response->json('public_id'), 'url' => $response->json('secure_url')];
    }

    public function delete(?array $logo): void
    {
        if (! $logo) {
            return;
        }

        try {
            if (! empty($logo['public_id'])) {
                $parameters = $this->signed([
                    'public_id' => $logo['public_id'],
                    'invalidate' => 'true',
                    'timestamp' => (string) time(),
                ]);
                $response = Http::asForm()->connectTimeout(5)->timeout(10)
                    ->post($this->endpoint('destroy'), $parameters);
                if (! $response->successful() || ! in_array($response->json('result'), ['ok', 'not found'], true)) {
                    Log::warning('Could not delete old Cloudinary logo.', ['public_id' => $logo['public_id']]);
                }
            } elseif (! empty($logo['path'])) {
                Storage::disk('public')->delete($logo['path']);
            }
        } catch (Throwable) {
            Log::warning('Could not clean up company logo.');
        }
    }

    private function signed(array $parameters): array
    {
        $config = config('services.cloudinary');
        abort_unless($config['cloud_name'] && $config['api_key'] && $config['api_secret'], 503, 'Chưa cấu hình đầy đủ Cloudinary trên backend.');
        ksort($parameters);
        $parts = [];
        foreach ($parameters as $key => $value) {
            $parts[] = $key.'='.$value;
        }

        return $parameters + [
            'api_key' => $config['api_key'],
            'signature' => hash('sha256', implode('&', $parts).$config['api_secret']),
        ];
    }

    private function endpoint(string $action): string
    {
        return 'https://api.cloudinary.com/v1_1/'.rawurlencode(config('services.cloudinary.cloud_name')).'/image/'.$action;
    }
}
