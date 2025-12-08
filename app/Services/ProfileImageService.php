<?php
namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileImageService
{
    protected string $bucket;
    protected ?string $keyFilePath;

    public function __construct()
    {
        $this->bucket = (string) config('filesystems.disks.gcs.bucket');
        $this->keyFilePath = config('filesystems.disks.gcs.key_file_path');
    }

    /**
     * Upload a profile image to Google Cloud Storage.
     *
     * @return array{gsutil_uri:string,fileName:string,path:string}
     */
    public function uploadImageToGCS(UploadedFile $file, string $dir = 'profile-images'): array
    {
        $extension = $file->getClientOriginalExtension();
        $filename = now()->format('Ymd_His')."_".Str::random(8).($extension ? ".{$extension}" : '');
        $objectName = trim($dir.'/' . $filename, '/');

        // Use Flysystem disk to upload
        $stream = fopen($file->getRealPath(), 'r');
        Storage::disk('gcs')->put($objectName, $stream, [
            'visibility' => 'private',
        ]);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $gsutilUri = sprintf('gs://%s/%s', $this->bucket, $objectName);

        return [
            'gsutil_uri' => $gsutilUri,
            'fileName'   => $filename,
            'path'       => $objectName,
        ];
    }

    /**
     * Generate a signed URL for a given GCS object URI (gs://bucket/path) or object path.
     *
     * @return array{signedUrl:string|null,fileName:string|null}
     */
    public function getImageUrl(string $uriOrPath, int $ttlMinutes = 60): array
    {
        [$bucket, $object] = $this->parseGsUri($uriOrPath);

        if (empty($object)) {
            return ['signedUrl' => null, 'fileName' => null];
        }

        $client = $this->getStorageClient();
        $bucketRef = $client->bucket($bucket ?: $this->bucket);
        $objectRef = $bucketRef->object($object);

        if (! $objectRef->exists()) {
            return ['signedUrl' => null, 'fileName' => basename($object) ?: null];
        }

        $expiresAt = (new \DateTimeImmutable())->modify('+' . $ttlMinutes . ' minutes');
        $signedUrl = $objectRef->signedUrl($expiresAt, [
            'version' => 'v4',
        ]);

        return [
            'signedUrl' => $signedUrl,
            'fileName'  => basename($object) ?: null,
        ];
    }

    /**
     * Delete an image from GCS given a gs:// URI or an object path.
     */
    public function deleteImageFromGCS(string $uriOrPath): bool
    {
        [$bucket, $object] = $this->parseGsUri($uriOrPath);
        if (empty($object)) {
            return false;
        }

        $client = $this->getStorageClient();
        $bucketRef = $client->bucket($bucket ?: $this->bucket);
        $objectRef = $bucketRef->object($object);

        if (! $objectRef->exists()) {
            return true; // Already gone
        }

        try {
            $objectRef->delete();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Update the CORS configuration for a bucket.
     */
    public function updateCorsConfiguration(
        ?string $bucketName = null,
        ?array $methods = null,
        ?array $origins = null,
        ?array $responseHeaders = null,
        ?int $maxAgeSeconds = null
    ): void {
        $bucketName = $bucketName ?: $this->bucket;
        $corsConfig = config('filesystems.disks.gcs.cors', []);

        $methods = $methods ?? ($corsConfig['methods'] ?? ['GET']);
        $origins = $origins ?? ($corsConfig['origins'] ?? []);
        $responseHeaders = $responseHeaders ?? ($corsConfig['response_headers'] ?? ['Content-Type']);
        $maxAgeSeconds = $maxAgeSeconds ?? ($corsConfig['max_age_seconds'] ?? 3600);

        $client = $this->getStorageClient();
        $bucket = $client->bucket($bucketName);

        $bucket->update([
            'cors' => [[
                'method' => array_values($methods),
                'origin' => array_values($origins),
                'responseHeader' => array_values($responseHeaders),
                'maxAgeSeconds' => $maxAgeSeconds,
            ]],
        ]);
    }

    protected function getStorageClient(): StorageClient
    {
        $config = [];
        if (! empty($this->keyFilePath)) {
            $config['keyFilePath'] = $this->keyFilePath;
        }
        if (! empty(config('filesystems.disks.gcs.project_id'))) {
            $config['projectId'] = config('filesystems.disks.gcs.project_id');
        }
        return new StorageClient($config);
    }

    /**
     * @return array{0:string|null,1:string|null} [bucket, objectName]
     */
    protected function parseGsUri(string $uriOrPath): array
    {
        $uriOrPath = trim($uriOrPath);
        if (str_starts_with($uriOrPath, 'gs://')) {
            $withoutScheme = substr($uriOrPath, 5);
            $parts = explode('/', $withoutScheme, 2);
            $bucket = $parts[0] ?? $this->bucket;
            $object = $parts[1] ?? null;
            return [$bucket, $object];
        }

        // Treat as object path in the default bucket
        return [null, ltrim($uriOrPath, '/') ?: null];
    }
}
