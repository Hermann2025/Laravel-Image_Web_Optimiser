<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ZipExtractorService
{
    public function extract(UploadedFile $file, string $sessionId): array
    {
        $zip = new ZipArchive();
        $extractedFiles = [];

        $tempPath = $file->storeAs("temp/zip/{$sessionId}", $file->getClientOriginalName(), 'public');
        $fullPath = Storage::disk('public')->path($tempPath);

        if ($zip->open($fullPath) === true) {
            $allowedTypes = config('image-optimizer.limits.allowed_types', ['jpeg', 'png', 'gif', 'webp', 'bmp']);
            $maxFiles = config('image-optimizer.limits.max_files', 20);
            $count = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ($count >= $maxFiles) break;

                $filename = $zip->getNameIndex($i);
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($extension, $allowedTypes)) {
                    $content = $zip->getFromIndex($i);
                    $originalName = basename($filename);
                    $path = "temp/images/{$sessionId}/{$originalName}";
                    Storage::disk('public')->put($path, $content);
                    $extractedFiles[] = [
                        'original_name' => $originalName,
                        'path' => $path,
                        'size' => strlen($content),
                        'mime' => $this->getMimeType($extension),
                    ];
                    $count++;
                }
            }

            $zip->close();
        }

        // Nettoyer le ZIP
        Storage::disk('public')->delete($tempPath);

        return $extractedFiles;
    }

    private function getMimeType(string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            default => 'application/octet-stream',
        };
    }
}