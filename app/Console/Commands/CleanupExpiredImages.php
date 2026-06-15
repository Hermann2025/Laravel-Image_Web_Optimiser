<?php

namespace App\Console\Commands;

use App\Services\ImageCleanupService;
use Illuminate\Console\Command;

class CleanupExpiredImages extends Command
{
    protected $signature = 'images:cleanup-expired';
    protected $description = 'Nettoie les images expirées du stockage temporaire';

    private ImageCleanupService $cleanupService;

    public function __construct(ImageCleanupService $cleanupService)
    {
        parent::__construct();
        $this->cleanupService = $cleanupService;
    }

    public function handle(): int
    {
        $count = $this->cleanupService->cleanupExpired();

        $this->info("Nettoyage terminé : {$count} image(s) supprimée(s).");

        return Command::SUCCESS;
    }
}