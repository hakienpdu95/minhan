<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Media\MediaUploadService;
use App\Shared\Tenancy\OrganizationScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cleanup jodit_content orphan media records that have not been associated
 * to a real entity within the TTL window (default 72 hours from last_touched_at).
 *
 * Also removes empty JoditDraft records (no remaining jodit_content media).
 *
 * Usage:
 *   php artisan media:cleanup-orphans
 *   php artisan media:cleanup-orphans --dry-run
 */
class MediaCleanupOrphansCommand extends Command
{
    protected $signature = 'media:cleanup-orphans
                            {--dry-run : Print what would be deleted without actually deleting}';

    protected $description = 'Delete Jodit orphan media (jodit_content) older than TTL, then prune empty draft records';

    public function __construct(private readonly MediaUploadService $uploadService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $ttlHours = (int) config('media.jodit_orphan_ttl_hours', 72);
        $cutoff   = now()->subHours($ttlHours);
        $isDryRun = $this->option('dry-run');

        $orphans = Media::withoutTenant()
            ->where('collection_name', 'jodit_content')
            ->where('model_type', \App\Models\JoditDraft::class)
            ->where(function ($q) use ($cutoff) {
                $q->where('last_touched_at', '<', $cutoff)
                  ->orWhereNull('last_touched_at');
            })
            ->get();

        if ($orphans->isEmpty()) {
            $this->info('No orphan media found.');
        } else {
            $this->info("Found {$orphans->count()} orphan media older than {$ttlHours}h." . ($isDryRun ? ' [DRY RUN]' : ''));
        }

        $deletedMedia = 0;

        foreach ($orphans as $media) {
            if ($isDryRun) {
                $this->line("  Would delete media: {$media->uuid} — {$media->file_name}");
                continue;
            }

            try {
                $this->uploadService->delete($media);
                $deletedMedia++;
            } catch (\Throwable $e) {
                Log::error('media:cleanup-orphans failed for media', [
                    'media_id' => $media->id,
                    'uuid'     => $media->uuid,
                    'error'    => $e->getMessage(),
                ]);
                $this->warn("  Failed to delete {$media->uuid}: {$e->getMessage()}");
            }
        }

        if (! $isDryRun && $deletedMedia > 0) {
            $this->info("Deleted {$deletedMedia} orphan media file(s).");
        }

        // --- Clean up empty JoditDraft records ---
        $emptyDraftQuery = \App\Models\JoditDraft::withoutGlobalScope(OrganizationScope::class)
            ->where('created_at', '<', $cutoff)
            ->whereDoesntHave('media', fn ($q) => $q->where('collection_name', 'jodit_content'));

        if ($isDryRun) {
            $count = $emptyDraftQuery->count();
            if ($count > 0) {
                $this->line("  Would delete {$count} empty JoditDraft record(s).");
            }
        } else {
            $deletedDrafts = $emptyDraftQuery->delete();
            if ($deletedDrafts > 0) {
                $this->info("Deleted {$deletedDrafts} empty JoditDraft record(s).");
            }
        }

        return self::SUCCESS;
    }
}
