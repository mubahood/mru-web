<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Derives the home slider's responsive image set from source photographs
 * already on the public disk.
 *
 * This exists so the slider is reproducible from a fresh clone. The first
 * version of these images was made by hand with `sips` during development —
 * a one-off step nobody else could repeat, and the kind of thing that quietly
 * breaks the day the storage volume is rebuilt without whoever did that step
 * being in the room. `UniversityContentSeeder` names the slides; this command
 * is what makes the files it names actually exist.
 *
 * A hero photo sits under a heavy navy scrim (see hero-slider.blade.php), so
 * it tolerates far more compression than a gallery image before anyone could
 * tell — hence the low quality numbers at every tier.
 */
class MakeHeroImages extends Command
{
    protected $signature = 'mru:make-hero-images {--force : Regenerate even if the derived files already exist}';

    protected $description = 'Generate the responsive (700/1100/1600px) home-slider image set from source photographs';

    /** Base name => source path relative to the public disk. */
    private const SOURCES = [
        'hero-engineering' => 'university/hero/source-engineering.jpg',
        'hero-agriculture' => 'university/hero/source-agriculture.jpg',
        'hero-graduation' => 'university/hero/slide_1783846485_3a6e5459.jpg',
        'hero-international' => 'university/hero/slide_1784646836_c86c18c2.jpg',
        'hero-heritage' => 'news/WhatsApp-Image-2025-11-26-at-11.48.59.jpeg',
        'hero-scholarship' => 'news/18B9190E-57D9-459A-AA2F-949CBE6AC5F0-1-scaled.jpeg',
        'hero-student-voice' => 'news/DSC_0945-scaled.jpg',
        'hero-classroom' => 'news/DSC_9769-scaled.jpg',
    ];

    /** width => JPEG quality. Lower at every step down: a smaller frame hides compression the eye would catch at full size. */
    private const TIERS = [1600 => 52, 1100 => 55, 700 => 60];

    public function handle(): int
    {
        $manager = ImageManager::gd();
        $disk = Storage::disk('public');
        $made = 0;
        $skipped = 0;

        foreach (self::SOURCES as $name => $source) {
            if (! $disk->exists($source)) {
                $this->error("Missing source photograph: {$source} — has mru:import-legacy run?");

                continue;
            }

            foreach (self::TIERS as $width => $quality) {
                $dest = "university/hero/{$name}-{$width}.jpg";

                if ($disk->exists($dest) && ! $this->option('force')) {
                    $skipped++;

                    continue;
                }

                $image = $manager->read($disk->path($source))->scale(width: $width);
                $encoded = $image->encode(new JpegEncoder(quality: $quality));
                $disk->put($dest, (string) $encoded);

                $made++;
                $this->line(sprintf('  %s  (%d KB)', $dest, (int) round(strlen((string) $encoded) / 1024)));
            }
        }

        $this->info("Hero images: {$made} written, {$skipped} already present (--force to regenerate).");

        return self::SUCCESS;
    }
}
