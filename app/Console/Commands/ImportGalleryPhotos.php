<?php

namespace App\Console\Commands;

use App\Models\GalleryPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Imports photographs from a folder, optimising each one on the way in.
 *
 * Phone photos arrive at 2304px and up to 700KB apiece, carrying whatever EXIF
 * the camera wrote, which on many devices includes GPS coordinates. Serving
 * them untouched would push several megabytes at every visitor and republish
 * any location data along with them. Each image is therefore re-encoded:
 * orientation baked in, all metadata stripped, resized to a sane maximum, and
 * written as both JPEG and WebP with a small grid thumbnail.
 *
 * Re-running is safe. A photo already imported under the same source name is
 * skipped unless --force is given.
 *
 * Two modes. Without --manifest the whole folder comes in and each title is
 * derived from its filename, which suits a bulk drop of already-named files.
 * With --manifest only the listed files are imported, under the slug, title,
 * caption, alt text and category the manifest gives them — the way a folder of
 * camera filenames (460A4341.JPG) becomes a curated set. The manifest is a
 * JSON array of {file, slug, title, caption, alt, category, sort} objects and
 * lives in the repository, so the selection is reviewable in a diff.
 *
 * The manifest supplies the copy on first import only. After that the record
 * belongs to whoever edits it in the admin gallery, and a re-run leaves their
 * wording alone unless --force says otherwise.
 */
class ImportGalleryPhotos extends Command
{
    protected $signature = 'gallery:import
        {source : Folder holding the original photographs}
        {--force : Re-process and overwrite photos already imported}
        {--manifest= : JSON file naming and describing the photographs to import}
        {--max=1600 : Longest edge, in pixels, of the full-size image}
        {--quality=82 : JPEG quality}';

    protected $description = 'Import and optimise photographs into the gallery';

    public function handle(): int
    {
        $source = rtrim((string) $this->argument('source'), '/');

        if (! is_dir($source)) {
            $this->error("Not a folder: {$source}");

            return self::FAILURE;
        }

        $manifest = $this->manifest();

        if ($manifest === false) {
            return self::FAILURE;
        }

        if (! $this->hasImageMagick()) {
            $this->error('ImageMagick (magick) is required and was not found on PATH.');

            return self::FAILURE;
        }

        Storage::disk('public')->makeDirectory('gallery');
        Storage::disk('public')->makeDirectory('gallery/thumbs');

        $files = collect(scandir($source) ?: [])
            ->reject(fn ($f) => str_starts_with($f, '.'))
            ->filter(fn ($f) => is_file("{$source}/{$f}"))
            ->values();

        if ($manifest !== null) {
            $missing = collect($manifest)->keys()->reject(fn ($f) => $files->contains($f));

            if ($missing->isNotEmpty()) {
                $this->error('Named in the manifest but not in the folder: '.$missing->implode(', '));

                return self::FAILURE;
            }

            // Manifest order, not directory order, so sort_order and the
            // progress bar both follow the curation.
            $files = collect($manifest)->keys()->values();
        }

        if ($files->isEmpty()) {
            $this->warn('No files found.');

            return self::SUCCESS;
        }

        $max = (int) $this->option('max');
        $quality = (int) $this->option('quality');
        $imported = 0;
        $skipped = 0;
        $savedBytes = 0;

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        foreach ($files as $index => $filename) {
            $entry = $manifest[$filename] ?? null;
            $slug = $entry['slug'] ?? (Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'photo-'.$index);
            $existing = GalleryPhoto::where('path', "gallery/{$slug}.jpg")->first();

            if ($existing && ! $this->option('force')) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $original = "{$source}/{$filename}";
            $originalBytes = (int) filesize($original);

            $jpeg = Storage::disk('public')->path("gallery/{$slug}.jpg");
            $webp = Storage::disk('public')->path("gallery/{$slug}.webp");
            $thumb = Storage::disk('public')->path("gallery/thumbs/{$slug}.jpg");

            // -auto-orient bakes the EXIF rotation in before -strip removes the
            // tag, so a portrait photo does not end up sideways once the
            // metadata (including GPS) is gone.
            $this->convert(['magick', $original, '-auto-orient', '-strip',
                '-resize', "{$max}x{$max}>", '-interlace', 'Plane',
                '-quality', (string) $quality, $jpeg]);

            $this->convert(['magick', $jpeg, '-quality', '78', $webp]);

            $this->convert(['magick', $jpeg, '-resize', '800x800>',
                '-quality', '78', $thumb]);

            if (! is_file($jpeg)) {
                $this->newLine();
                $this->warn("Could not process {$filename}");
                $bar->advance();

                continue;
            }

            [$width, $height] = getimagesize($jpeg) ?: [null, null];
            $newBytes = (int) filesize($jpeg);
            $savedBytes += max(0, $originalBytes - $newBytes);

            $attributes = [
                'title' => $existing->title ?? $entry['title'] ?? Str::headline($slug),
                'caption' => $existing->caption ?? $entry['caption'] ?? null,
                'alt' => $existing->alt ?? $entry['alt'] ?? null,
                'category' => $existing->category ?? $entry['category'] ?? null,
                'webp_path' => is_file($webp) ? "gallery/{$slug}.webp" : null,
                'thumb_path' => is_file($thumb) ? "gallery/thumbs/{$slug}.jpg" : null,
                'width' => $width,
                'height' => $height,
                'bytes' => $newBytes,
                'sort_order' => $existing->sort_order ?? $entry['sort'] ?? $index,
            ];

            // A photograph named in the manifest was picked to be seen, so it
            // arrives published. A bulk drop keeps the column's own default and
            // whatever an editor has since decided about it.
            if ($entry !== null && ! $existing) {
                $attributes['is_published'] = true;
            }

            GalleryPhoto::updateOrCreate(['path' => "gallery/{$slug}.jpg"], $attributes);

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Imported {$imported}, skipped {$skipped} (already present).");
        $this->line('Saved '.round($savedBytes / 1048576, 2).' MB against the originals.');

        return self::SUCCESS;
    }

    /**
     * The curation, keyed by source filename.
     *
     * @return array<string, array<string, mixed>>|null|false  null when no
     *   manifest was asked for, false when the one asked for cannot be used.
     */
    private function manifest(): array|null|false
    {
        $path = (string) $this->option('manifest');

        if ($path === '') {
            return null;
        }

        if (! is_file($path)) {
            $this->error("No such manifest: {$path}");

            return false;
        }

        $rows = json_decode((string) file_get_contents($path), true);

        if (! is_array($rows) || $rows === []) {
            $this->error("Manifest is not a non-empty JSON array: {$path}");

            return false;
        }

        $keyed = [];

        foreach ($rows as $i => $row) {
            foreach (['file', 'slug', 'title', 'alt'] as $required) {
                if (empty($row[$required])) {
                    $this->error("Manifest entry {$i} has no {$required}.");

                    return false;
                }
            }

            $keyed[$row['file']] = $row;
        }

        return $keyed;
    }

    private function hasImageMagick(): bool
    {
        $probe = new Process(['magick', '-version']);
        $probe->run();

        return $probe->isSuccessful();
    }

    /**
     * Named convert() rather than run(): Command::run() already exists and is
     * public, so a private run() here is a fatal error at class load.
     *
     * @param  list<string>  $command
     */
    private function convert(array $command): void
    {
        $process = new Process($command, timeout: 120);
        $process->run();
    }
}
