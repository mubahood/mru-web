<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Re-encodes the photographs already on the public disk so a visitor is not
 * made to download an image many times larger than the box it is drawn in.
 *
 * The legacy imports arrived at whatever size the phone, camera or WordPress
 * "-scaled" pipeline produced — 2560px JPEGs and, worse, megabyte PNGs of
 * photographs, a format that stores no better than a bitmap for this content.
 * Nothing on the site draws an image wider than 1600px.
 *
 * Two rules make this safe to run against live content:
 *
 *  - The filename and extension never change. Every path is referenced from a
 *    database row somewhere (posts, staff photos, hero slides), and renaming
 *    would silently break references that no test covers.
 *  - Nothing is ever enlarged, and an image already smaller than the cap is
 *    only re-encoded if that actually saves bytes; the original is restored
 *    if it does not.
 *
 * Originals are copied to storage/app/image-originals before the first change
 * to any file, so the pass is reversible.
 */
class OptimiseImages extends Command
{
    protected $signature = 'mru:optimise-images
        {--dirs=news,university,gallery,faculties,scholar : Comma-separated folders on the public disk}
        {--max=1600 : Longest edge, in pixels}
        {--quality=82 : JPEG quality}
        {--min-saving=3 : Skip a file that would shrink by less than this percent}
        {--dry-run : Report what would change and write nothing}';

    protected $description = 'Cap and re-encode oversized photographs on the public disk';

    public function handle(): int
    {
        if (! $this->hasMagick()) {
            $this->error('ImageMagick (magick) is required and was not found on PATH.');

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $max = (int) $this->option('max');
        $quality = (int) $this->option('quality');
        $minSaving = (float) $this->option('min-saving');
        $dry = (bool) $this->option('dry-run');

        $files = [];
        foreach (explode(',', (string) $this->option('dirs')) as $dir) {
            foreach ($disk->allFiles(trim($dir)) as $path) {
                if (preg_match('/\.(jpe?g|png)$/i', $path)) {
                    $files[] = $path;
                }
            }
        }

        if ($files === []) {
            $this->warn('No images found.');

            return self::SUCCESS;
        }

        $before = 0;
        $after = 0;
        $changed = 0;
        $rows = [];

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $path) {
            $full = $disk->path($path);
            $size = (int) filesize($full);
            $before += $size;

            $new = $dry ? $this->wouldBe($full, $max, $quality) : $this->rewrite($disk, $path, $full, $max, $quality, $minSaving);

            $after += $new;

            if ($new > 0 && $new < $size * (1 - $minSaving / 100)) {
                $changed++;
                if (count($rows) < 12) {
                    $rows[] = [$path, $this->mb($size), $this->mb($new), round(100 - $new / $size * 100).'%'];
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($rows !== []) {
            $this->table(['File', 'Before', 'After', 'Saved'], $rows);
            $this->line('(largest savings shown)');
        }

        $this->info(sprintf('%s%d of %d files: %s -> %s, saving %s (%d%%).',
            $dry ? 'DRY RUN — ' : '', $changed, count($files),
            $this->mb($before), $this->mb($after), $this->mb($before - $after),
            $before > 0 ? round(100 - $after / $before * 100) : 0));

        return self::SUCCESS;
    }

    /** Re-encode in place, keeping the original aside and reverting if it did not help. */
    private function rewrite($disk, string $path, string $full, int $max, int $quality, float $minSaving): int
    {
        $size = (int) filesize($full);
        $backup = storage_path('app/image-originals/'.$path);

        if (! is_file($backup)) {
            @mkdir(dirname($backup), 0775, true);
            copy($full, $backup);
        }

        $tmp = $full.'.opt.tmp';
        $isPng = (bool) preg_match('/\.png$/i', $path);

        // -resize with '>' only ever shrinks. Interlacing costs nothing and
        // lets a slow connection paint the whole frame early.
        $cmd = ['magick', $backup, '-auto-orient', '-strip', '-resize', "{$max}x{$max}>"];
        $cmd = array_merge($cmd, $isPng
            ? ['-define', 'png:compression-level=9', 'PNG:'.$tmp]
            : ['-interlace', 'Plane', '-sampling-factor', '4:2:0', '-quality', (string) $quality, 'JPEG:'.$tmp]);

        $this->convert($cmd);

        if (! is_file($tmp)) {
            return $size;
        }

        $new = (int) filesize($tmp);

        // Never make a file bigger, and do not churn one for a rounding error.
        if ($new <= 0 || $new >= $size * (1 - $minSaving / 100)) {
            @unlink($tmp);

            return $size;
        }

        rename($tmp, $full);

        return $new;
    }

    private function wouldBe(string $full, int $max, int $quality): int
    {
        $tmp = sys_get_temp_dir().'/mru-opt-'.md5($full).'.jpg';
        $this->convert(['magick', $full, '-auto-orient', '-strip', '-resize', "{$max}x{$max}>",
            '-interlace', 'Plane', '-sampling-factor', '4:2:0', '-quality', (string) $quality, 'JPEG:'.$tmp]);
        $n = is_file($tmp) ? (int) filesize($tmp) : (int) filesize($full);
        @unlink($tmp);

        return min($n, (int) filesize($full));
    }

    private function mb(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }

    /**
     * Named convert(), not run(): Command::run() already exists and is public,
     * so a private run() here is a fatal error at class load — the same trap
     * ImportGalleryPhotos documents.
     *
     * @param  list<string>  $command
     */
    private function convert(array $command): void
    {
        (new Process($command, timeout: 180))->run();
    }

    private function hasMagick(): bool
    {
        $p = new Process(['magick', '-version']);
        $p->run();

        return $p->isSuccessful();
    }
}
