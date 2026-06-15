<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\OptimizedImage;
use App\Services\ImageCleanupService;
use App\Services\ImageOptimizerService;
use App\Services\ZipExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerController extends Controller
{
    private ImageOptimizerService $optimizerService;
    private ZipExtractorService $zipExtractor;
    private ImageCleanupService $cleanupService;

    public function __construct(
        ImageOptimizerService $optimizerService,
        ZipExtractorService $zipExtractor,
        ImageCleanupService $cleanupService
    ) {
        $this->optimizerService = $optimizerService;
        $this->zipExtractor = $zipExtractor;
        $this->cleanupService = $cleanupService;
    }

    // ==================== WEB ROUTES ====================

    public function index()
    {
        return view('optimizer.index');
    }

    public function results(string $sessionId)
    {
        $images = OptimizedImage::bySession($sessionId)
            ->orderBy('created_at')
            ->get();

        if ($images->isEmpty()) {
            return redirect()->route('optimizer.index')
                ->with('error', 'Session introuvable ou expirée.');
        }

        return view('optimizer.results', compact('images', 'sessionId'));
    }

    // ==================== API/UPLOAD ====================

    public function upload(ImageUploadRequest $request): JsonResponse
    {
        // Réutiliser le sessionId existant envoyé par le frontend
        $sessionId = $request->input('session_id', Str::uuid()->toString());
        $uploadedImages = [];

        // Récupérer le(s) fichier(s) - peut être un tableau ou un fichier unique
        $files = $request->file('images');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (!$file) continue;

            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'zip') {
                $extracted = $this->zipExtractor->extract($file, $sessionId);
                foreach ($extracted as $item) {
                    $uploadedImages[] = $this->createImageRecord($sessionId, $item);
                }
            } else {
                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs("temp/images/{$sessionId}", $originalName, 'public');
                $uploadedImages[] = $this->createImageRecord($sessionId, [
                    'original_name' => $originalName,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'images' => $uploadedImages,
            'count' => count($uploadedImages),
        ]);
    }

    public function optimize(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'quality' => 'nullable|integer|min:10|max:100',
            'format' => 'nullable|in:webp,avif,jpeg,png,original',
            'max_width' => 'nullable|integer|min:100|max:4096',
        ]);

        $sessionId = $request->input('session_id');
        $options = $request->only(['quality', 'format', 'max_width']);

        $images = OptimizedImage::bySession($sessionId)
            ->where('status', 'pending')
            ->get();

        if ($images->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune image en attente à optimiser.',
            ], 400);
        }

        $results = [];
        foreach ($images as $image) {
            try {
                $result = $this->optimizerService->optimize($image, $options);
                $results[] = [
                    'id' => $result->id,
                    'original_name' => $result->original_name,
                    'original_size' => $result->original_size,
                    'optimized_size' => $result->optimized_size,
                    'compression_ratio' => $result->compression_ratio,
                    'gain' => $result->gain,
                    'format' => $result->format_converted_to,
                    'status' => $result->status,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $image->id,
                    'original_name' => $image->original_name,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'session_id' => $sessionId,
        ]);
    }

    public function status(string $sessionId): JsonResponse
    {
        $images = OptimizedImage::bySession($sessionId)->orderBy('created_at')->get();

        return response()->json([
            'success' => true,
            'images' => $images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'original_name' => $image->original_name,
                    'original_size' => $image->original_size,
                    'optimized_size' => $image->optimized_size,
                    'compression_ratio' => $image->compression_ratio,
                    'gain' => $image->gain,
                    'format' => $image->format_converted_to,
                    'status' => $image->status,
                    'variants' => $image->variants,
                ];
            }),
        ]);
    }

    // ==================== DOWNLOAD ====================

    public function download(int $id)
    {
        $image = OptimizedImage::findOrFail($id);

        if ($image->status !== 'completed' || !$image->path_optimized) {
            return response()->json(['error' => 'Image non disponible.'], 404);
        }

        if (!Storage::disk('public')->exists($image->path_optimized)) {
            return response()->json(['error' => 'Fichier introuvable.'], 404);
        }

        // Marquer comme téléchargé
        $image->update(['downloaded' => true]);

        return Storage::disk('public')->download(
            $image->path_optimized,
            $image->original_name
        );
    }

    public function downloadAll(string $sessionId)
    {
        $images = OptimizedImage::bySession($sessionId)
            ->where('status', 'completed')
            ->get();

        if ($images->isEmpty()) {
            return response()->json(['error' => 'Aucune image à télécharger.'], 404);
        }

        $zipFileName = "optimisees_{$sessionId}.zip";
        $zipPath = storage_path("app/public/temp/zip/{$zipFileName}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json(['error' => 'Erreur lors de la création du ZIP.'], 500);
        }

        foreach ($images as $image) {
            $filePath = Storage::disk('public')->path($image->path_optimized);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $image->original_name);
            }
        }

        $zip->close();

        $this->cleanupService->markAsDownloaded($sessionId);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // ==================== DELETE / CLEANUP ====================

    public function delete(int $id): JsonResponse
    {
        $image = OptimizedImage::findOrFail($id);
        $this->optimizerService->cleanup($image);
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image supprimée.']);
    }

    public function deleteSession(string $sessionId): JsonResponse
    {
        $this->cleanupService->cleanupBySession($sessionId);

        return response()->json(['success' => true, 'message' => 'Session nettoyée.']);
    }

    // ==================== PRIVATE ====================

    private function createImageRecord(string $sessionId, array $data): array
    {
        $expiresAt = now()->addHours(config('image-optimizer.cleanup.expires_after_hours', 1));

        $image = OptimizedImage::create([
            'session_id' => $sessionId,
            'original_name' => $data['original_name'],
            'original_size' => $data['size'],
            'mime_type' => $data['mime'],
            'path_original' => $data['path'],
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        return [
            'id' => $image->id,
            'original_name' => $image->original_name,
            'size' => $image->original_size,
            'mime' => $image->mime_type,
            'preview_url' => Storage::disk('public')->url($image->path_original),
        ];
    }
}