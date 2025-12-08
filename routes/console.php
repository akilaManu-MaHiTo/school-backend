<?php

use App\Services\ProfileImageService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('gcs:cors-update {--bucket=} {--method=*} {--origin=*} {--response-header=*} {--max-age=}', function (ProfileImageService $profileImageService) {
    $bucket = $this->option('bucket') ?: config('filesystems.disks.gcs.bucket');

    if (! $bucket) {
        $this->error('No GCS bucket configured. Set GOOGLE_CLOUD_STORAGE_BUCKET or pass --bucket.');
        return Command::FAILURE;
    }

    $methods = array_filter((array) $this->option('method')) ?: null;
    $origins = array_filter((array) $this->option('origin')) ?: null;
    $responseHeaders = array_filter((array) $this->option('response-header')) ?: null;
    $maxAge = $this->option('max-age');
    $maxAgeSeconds = $maxAge !== null ? (int) $maxAge : null;

    $profileImageService->updateCorsConfiguration(
        $bucket,
        $methods,
        $origins,
        $responseHeaders,
        $maxAgeSeconds
    );

    $this->info(sprintf('Updated CORS configuration for bucket %s.', $bucket));

    return Command::SUCCESS;
})->purpose('Update Google Cloud Storage bucket CORS rules');
