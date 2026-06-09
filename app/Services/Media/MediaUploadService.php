<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\MediaLibrary\HasMedia;

class MediaUploadService
{
    public function __construct(private readonly MediaUrlService $urlService) {}

    /**
     * Upload a file, store it, create a Media record, and run synchronous conversions.
     *
     * @param  array{alt_text?: string, caption?: string}  $options
     */
    public function upload(
        UploadedFile $file,
        HasMedia&Model $model,
        string $collection,
        array $options = []
    ): Media {
        $collectionConfig = $this->collectionConfig($collection);

        $this->validateFile($file, $collectionConfig);

        $disk = $collectionConfig['disk'] ?? config('media.disk', 'public');

        /** @var Media $media */
        $media = $model->addMedia($file)
            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName($file->getClientOriginalName())
            ->withCustomProperties([
                'is_public'      => $collectionConfig['is_public'] ?? true,
                'uploaded_by'    => auth()->id(),
                'module'         => $this->resolveModule($model),
                'alt_text'       => $options['alt_text'] ?? '',
                'caption'        => $options['caption'] ?? '',
                'file_type'      => $options['file_type'] ?? null,
                'application_id' => $options['application_id'] ?? null,
            ])
            ->toMediaCollection($collection, $disk);

        // Set organization_id and uploaded_at explicitly (not handled by Spatie)
        $media->organization_id = TenantContext::getOrganizationId();
        $media->uploaded_at     = now();
        $media->save();

        // Synchronous image conversions (no queue)
        $this->runConversions($media, $collectionConfig['conversions'] ?? []);

        return $media->fresh();
    }

    /**
     * Delete a media record and its associated files on disk.
     * Conversions are stored manually alongside the original (not in Spatie's
     * conversions/ subdir), so we delete them explicitly before the record.
     * After deletion we prune empty directories: hash folder and numeric-ID parent.
     */
    public function delete(Media $media): void
    {
        $disk     = $media->disk;
        $basePath = rtrim(dirname($media->getPathRelativeToRoot()), '/');

        foreach ($media->generated_conversions ?? [] as $name => $generated) {
            if ($generated) {
                Storage::disk($disk)->delete($basePath . '/' . $name . '.webp');
            }
        }

        $media->delete(); // Spatie deletes original file via model observer

        // Prune now-empty hash directory (e.g. 5/a569415e-…/)
        if (empty(Storage::disk($disk)->files($basePath)) &&
            empty(Storage::disk($disk)->directories($basePath))) {
            Storage::disk($disk)->deleteDirectory($basePath);
        }

        // Prune parent numeric-ID directory (e.g. 5/) if also empty
        $parent = dirname($basePath);
        if ($parent !== '.' && $parent !== '/' &&
            empty(Storage::disk($disk)->files($parent)) &&
            empty(Storage::disk($disk)->directories($parent))) {
            Storage::disk($disk)->deleteDirectory($parent);
        }
    }

    /**
     * Delete all media in a collection for the given model.
     */
    public function bulkDelete(HasMedia&Model $model, string $collection): void
    {
        $model->clearMediaCollection($collection);
    }

    /**
     * Re-associate jodit_content orphan media to the real entity after content is saved.
     * Also hard-deletes any orphans of this entity not in the provided UUID list.
     *
     * @param  string[]  $uuids  UUIDs of media that should be kept and re-associated
     */
    public function reassociateOrphans(HasMedia&Model $model, array $uuids): void
    {
        if (empty($uuids)) {
            return;
        }

        Media::withoutTenant()
            ->whereIn('uuid', $uuids)
            ->where('collection_name', 'jodit_content')
            ->get()
            ->each(function (Media $media) use ($model) {
                $media->model_type = get_class($model);
                $media->model_id   = $model->getKey();
                $media->save();
            });

        // Clean up old jodit_content orphans for this entity that are no longer referenced
        Media::withoutTenant()
            ->where('model_type', get_class($model))
            ->where('model_id', $model->getKey())
            ->where('collection_name', 'jodit_content')
            ->whereNotIn('uuid', $uuids)
            ->get()
            ->each(fn (Media $m) => $m->delete());
    }

    /**
     * Run image conversions synchronously using Intervention Image v4.
     *
     * @param  string[]  $conversions
     */
    private function runConversions(Media $media, array $conversions): void
    {
        if (empty($conversions) || ! str_starts_with($media->mime_type ?? '', 'image/')) {
            return;
        }

        $disk            = $media->disk;
        $basePath        = rtrim(dirname($media->getPathRelativeToRoot()), '/');
        $originalContent = Storage::disk($disk)->get($media->getPathRelativeToRoot());

        if (! $originalContent) {
            return;
        }

        $generatedConversions = [];

        foreach ($conversions as $conversion) {
            $settings = config("media.conversion_settings.{$conversion}");
            if (! $settings) {
                continue;
            }

            try {
                $image = Image::decode($originalContent);

                $originalWidth = $image->width();

                // Skip upscaling for scale conversions
                if ($settings['method'] === 'scale' && $settings['width'] && $originalWidth <= $settings['width']) {
                    $generatedConversions[$conversion] = false;
                    continue;
                }

                if ($settings['method'] === 'crop') {
                    $image->cover($settings['width'], $settings['height']);
                } else {
                    $image->scaleDown(width: $settings['width']);
                }

                $encoded = $image->encode(new WebpEncoder($settings['quality']));

                Storage::disk($disk)->put(
                    $basePath . '/' . $conversion . '.webp',
                    (string) $encoded
                );

                $generatedConversions[$conversion] = true;
            } catch (\Throwable $e) {
                Log::error('Media conversion failed', [
                    'media_id'   => $media->id,
                    'conversion' => $conversion,
                    'error'      => $e->getMessage(),
                ]);
                $generatedConversions[$conversion] = false;
            }
        }

        $media->generated_conversions = $generatedConversions;
        $media->save();
    }

    private function validateFile(UploadedFile $file, array $collectionConfig): void
    {
        $maxKb = $collectionConfig['max_size_kb'] ?? 51200;
        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => ["File quá lớn. Tối đa {$maxKb} KB."],
            ]);
        }

        $allowedMime = $collectionConfig['allowed_mime'] ?? ['*'];
        if ($allowedMime !== ['*'] && ! in_array($file->getMimeType(), $allowedMime, true)) {
            throw ValidationException::withMessages([
                'file' => ['Loại file không được phép.'],
            ]);
        }
    }

    private function collectionConfig(string $collection): array
    {
        return config("media.collections.{$collection}", [
            'max_size_kb'  => 51200,
            'allowed_mime' => ['*'],
            'is_public'    => true,
            'conversions'  => [],
        ]);
    }

    private function resolveModule(Model $model): string
    {
        $class = get_class($model);
        if (str_starts_with($class, 'Modules\\')) {
            return strtolower(explode('\\', $class)[1] ?? 'core');
        }
        return match ($class) {
            \App\Models\JoditDraft::class => 'jodit',
            default                       => 'core',
        };
    }
}
