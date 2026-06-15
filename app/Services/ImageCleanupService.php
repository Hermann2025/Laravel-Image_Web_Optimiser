<?php

namespace App\Services;

use App\Models\OptimizedImage;

class ImageCleanupService
{
    private ImageOptimizerService $optimizerService;

    public function __construct(ImageOptimizerService $optimizerService)
    {
        $this->optimizerService = $optimizerService;
    }

    public function cleanupExpired(): int
    {
        $expiredImages = OptimizedImage::expired()->get();
        $count = 0;

        foreach ($expiredImages as $image) {
            $this->optimizerService->cleanup($image);
            $image->delete();
            $count++;
        }

        return $count;
    }

    public function cleanupBySession(string $sessionId): void
    {
        $images = OptimizedImage::bySession($sessionId)->get();

        foreach ($images as $image) {
            $this->optimizerService->cleanup($image);
            $image->delete();
        }
    }

    public function markAsDownloaded(string $sessionId): void
    {
        OptimizedImage::bySession($sessionId)
            ->where('status', 'completed')
            ->update(['downloaded' => true]);
    }
}