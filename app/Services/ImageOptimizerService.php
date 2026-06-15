<?php

namespace App\Services;

use App\Models\OptimizedImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageOptimizerService
{
    private ImageManager $manager;
    private array $config;

    public function __construct()
    {
        // Use GD driver (compatible sans extension imagick)
        $this->manager = ImageManager::gd();
        $this->config = config('image-optimizer');
    }

    public function optimize(OptimizedImage $image, array $options = []): OptimizedImage
    {
        $image->update(['status' => 'processing']);

        $quality = $options['quality'] ?? $this->config['quality']['jpeg'];
        $format = $options['format'] ?? 'webp';
        $maxWidth = $options['max_width'] ?? $this->config['resize']['max_width'];

        $originalPath = Storage::disk('public')->path($image->path_original);
        $originalName = pathinfo($image->original_name, PATHINFO_FILENAME);
        $extension = $format === 'original' ? pathinfo($image->original_name, PATHINFO_EXTENSION) : $format;

        $optimizedFileName = $originalName . '.' . $extension;
        $optimizedPath = 'temp/images/optimized/' . $image->session_id . '/' . $optimizedFileName;

        try {
            $img = $this->manager->decodePath($originalPath);

            // Redimensionner si nécessaire
            if ($maxWidth && $img->width() > $maxWidth) {
                $img->scale($maxWidth);
            }

            // Générer les thumbnails
            $variants = [];
            if ($this->config['thumbnails']['enabled']) {
                $variants = $this->generateThumbnails($img, $image);
            }

            // Convertir et optimiser
            $encoded = match ($format) {
                'webp' => $img->toWebp($quality),
                'avif' => $img->toAvif($quality),
                'png' => $img->toPng(),
                'gif' => $img->toGif(),
                default => $img->toJpeg($quality),
            };

            Storage::disk('public')->put($optimizedPath, $encoded);

            $optimizedSize = Storage::disk('public')->size($optimizedPath);
            $originalSize = Storage::disk('public')->size($image->path_original);
            $compressionRatio = $originalSize > 0
                ? round((1 - ($optimizedSize / $originalSize)) * 100, 1)
                : 0;

            $image->update([
                'path_optimized' => $optimizedPath,
                'optimized_size' => $optimizedSize,
                'original_size' => $originalSize,
                'compression_ratio' => $compressionRatio,
                'format_converted_to' => $format,
                'variants' => $variants,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            $image->update([
                'status' => 'failed',
            ]);
            throw $e;
        }

        return $image;
    }

    private function generateThumbnails($img, OptimizedImage $image): array
    {
        $variants = [];
        $sessionId = $image->session_id;
        $originalName = pathinfo($image->original_name, PATHINFO_FILENAME);

        foreach ($this->config['thumbnails']['sizes'] as $name => [$width, $height]) {
            $thumb = clone $img;
            $thumb->cover($width, $height);
            $thumbPath = "temp/images/thumbnails/{$sessionId}/{$originalName}_{$name}.webp";
            Storage::disk('public')->put($thumbPath, $thumb->toWebp(70));
            $variants[$name] = $thumbPath;
        }

        return $variants;
    }

    public function cleanup(OptimizedImage $image): void
    {
        $paths = [$image->path_original, $image->path_optimized];
        if ($image->variants) {
            foreach ($image->variants as $variantPath) {
                $paths[] = $variantPath;
            }
        }

        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Nettoyer les dossiers vides
        $this->cleanupEmptyDirectories($image->session_id);
    }

    private function cleanupEmptyDirectories(string $sessionId): void
    {
        $dirs = [
            "temp/images/{$sessionId}",
            "temp/images/optimized/{$sessionId}",
            "temp/images/thumbnails/{$sessionId}",
        ];

        foreach ($dirs as $dir) {
            $path = Storage::disk('public')->path($dir);
            if (is_dir($path) && count(scandir($path)) <= 2) {
                rmdir($path);
            }
        }
    }
}