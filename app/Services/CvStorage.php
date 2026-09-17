<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CvStorage
{
    public function upload(UploadedFile $file, int $applicantId): string
    {
        $publicId = 'itviec/cvs/'.$applicantId.'/'.Str::uuid().'.'.strtolower($file->getClientOriginalExtension());
        $parameters = $this->signed(['public_id' => $publicId, 'overwrite' => 'false']);

        try {
            $response = Http::connectTimeout(5)->timeout(30)
                ->attach('file', $file->getContent(), basename($publicId))
                ->post($this->endpoint('upload'), $parameters);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['cv' => 'Không kết nối được Cloudinary. Vui lòng thử lại.']);
        }

        if (! $response->successful() || $response->json('public_id') !== $publicId
            || $response->json('resource_type') !== 'raw' || $response->json('type') !== 'authenticated') {
            $this->delete($publicId);
            throw ValidationException::withMessages(['cv' => 'Không tải được CV lên Cloudinary. Vui lòng thử lại.']);
        }

        return $publicId;
    }

    public function delete(string $publicId): void
    {
        try {
            $response = Http::asForm()->connectTimeout(5)->timeout(10)
                ->post($this->endpoint('destroy'), $this->signed(['public_id' => $publicId]));
            if (! $response->successful() || ! in_array($response->json('result'), ['ok', 'not found'], true)) {
                Log::warning('Could not remove unused CV.', ['public_id' => $publicId]);
            }
        } catch (Throwable) {
            Log::warning('Could not remove unused CV.', ['public_id' => $publicId]);
        }
    }

    private function signed(array $parameters): array
    {
        $config = config('services.cloudinary');
        abort_unless($config['cloud_name'] && $config['api_key'] && $config['api_secret'], 503, 'Chưa cấu hình Cloudinary.');
        $parameters += ['type' => 'authenticated', 'timestamp' => (string) time()];
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
        return 'https://api.cloudinary.com/v1_1/'.rawurlencode(config('services.cloudinary.cloud_name')).'/raw/'.$action;
    }
}
