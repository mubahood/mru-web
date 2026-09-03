<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The command that makes the home slider reproducible from a fresh clone.
 *
 * The first version of these images was made by hand with `sips` — a step
 * nobody else could repeat, and exactly the kind of thing that quietly breaks
 * the day storage is rebuilt without whoever did that step being in the room.
 * This pins that it never regresses to being a manual step again.
 */
class MakeHeroImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Any decodable image stands in for the real photographs; the command
        // does not care what is in the frame, only that it can scale it.
        $pixel = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/2wBDAQMDAwQDBAgEBAgQCwkLEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBD/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAj/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        foreach (['university/hero/slide_1783846485_3a6e5459.jpg', 'university/hero/slide_1784646836_c86c18c2.jpg', 'news/WhatsApp-Image-2025-11-26-at-11.48.59.jpeg'] as $source) {
            Storage::disk('public')->put($source, $pixel);
        }
    }

    public function test_it_derives_the_full_responsive_set_from_the_sources(): void
    {
        $this->artisan('mru:make-hero-images')->assertExitCode(0);

        foreach (['hero-graduation', 'hero-international', 'hero-heritage'] as $name) {
            foreach ([700, 1100, 1600] as $width) {
                Storage::disk('public')->assertExists("university/hero/{$name}-{$width}.jpg");
            }
        }
    }

    public function test_running_it_again_leaves_existing_files_alone(): void
    {
        $this->artisan('mru:make-hero-images');
        $originalSize = Storage::disk('public')->size('university/hero/hero-graduation-1600.jpg');
        $originalMtime = Storage::disk('public')->lastModified('university/hero/hero-graduation-1600.jpg');

        sleep(1);
        $this->artisan('mru:make-hero-images')->expectsOutputToContain('9 already present');

        $this->assertSame($originalSize, Storage::disk('public')->size('university/hero/hero-graduation-1600.jpg'));
        $this->assertSame($originalMtime, Storage::disk('public')->lastModified('university/hero/hero-graduation-1600.jpg'),
            'a re-run without --force must not touch a file that already exists');
    }

    public function test_force_regenerates_every_file(): void
    {
        $this->artisan('mru:make-hero-images');

        $this->artisan('mru:make-hero-images', ['--force' => true])
            ->expectsOutputToContain('9 written, 0 already present');
    }

    public function test_a_missing_source_is_reported_and_does_not_abort_the_others(): void
    {
        Storage::disk('public')->delete('news/WhatsApp-Image-2025-11-26-at-11.48.59.jpeg');

        $this->artisan('mru:make-hero-images')
            ->expectsOutputToContain('Missing source photograph')
            ->assertExitCode(0);

        Storage::disk('public')->assertExists('university/hero/hero-graduation-1600.jpg');
        Storage::disk('public')->assertMissing('university/hero/hero-heritage-1600.jpg');
    }
}
