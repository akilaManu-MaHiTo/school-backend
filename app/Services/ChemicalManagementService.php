<?php
namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChemicalManagementService
{
    protected string $bucket;
    protected ?string $keyFilePath;

    public function __construct()
    {
        $this->bucket = (string) config('filesystems.disks.gcs.bucket');
        $this->keyFilePath = config('filesystems.disks.gcs.key_file_path');
    }

    /**
     * Upload a document to Google Cloud Storage for Chemical Management flows.
     * Returns an array compatible with controllers' expectations.
     *
     * @return array{gsutil_uri:string,file_name:string,path:string}
     */
    public function uploadImageToGCS(UploadedFile $file, string $dir = 'chemical-management'): array
    {
        $extension = $file->getClientOriginalExtension();
        $filename = now()->format('Ymd_His') . '_' . Str::random(8) . ($extension ? ".{$extension}" : '');
        $objectName = trim($dir . '/' . $filename, '/');

        $stream = fopen($file->getRealPath(), 'r');
        Storage::disk('gcs')->put($objectName, $stream, [
            'visibility' => 'private',
        ]);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return [
            'gsutil_uri' => sprintf('gs://%s/%s', $this->bucket, $objectName),
            'file_name'  => $filename,
            'path'       => $objectName,
        ];
    }

    /**
     * Alias for uploadImageToGCS used by update flows in controllers.
     *
     * @return array{gsutil_uri:string,file_name:string,path:string}
     */
    public function updateDocuments(UploadedFile $file, string $dir = 'chemical-management'): array
    {
        return $this->uploadImageToGCS($file, $dir);
    }

    /**
     * Generate a signed URL for a given gs:// URI or relative object path.
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
     * Remove a document from storage given a gs:// URI or object path.
     */
    public function removeOldDocumentFromStorage(string $uriOrPath): bool
    {
        return $this->deleteImageFromGCS($uriOrPath);
    }

    /**
     * Delete an object from GCS (gs://... or path).
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
            return true; // already removed
        }

        try {
            $objectRef->delete();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
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

        return [null, ltrim($uriOrPath, '/') ?: null];
    }
}
