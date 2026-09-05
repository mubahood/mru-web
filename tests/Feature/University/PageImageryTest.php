<?php

namespace Tests\Feature\University;

use App\Models\GalleryPhoto;
use App\Support\SiteNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every photograph on this site is a real one, chosen by opening it. That only
 * holds if the file the page names is actually there: a blade pointing at a
 * missing asset still returns 200, still passes every other test, and simply
 * shows a broken frame to the reader. Two of these got as far as a commit
 * before being caught by eye, so they are caught here instead.
 */
class PageImageryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The pages given a header photograph, and the file each one names.
     *
     * @return array<string, array{string, string}>
     */
    public static function pagesWithHeroPhotographs(): array
    {
        return [
            'library' => ['library', 'images/photos/hero-library.jpg'],
            'students guild' => ['guild', 'images/photos/hero-guild.jpg'],
            'governance' => ['governance', 'images/photos/hero-governance.jpg'],
            'university council' => ['council', 'images/photos/hero-council.jpg'],
            'staff directory' => ['staff.directory', 'images/photos/hero-staff.jpg'],
            'events' => ['events.index', 'images/photos/hero-events.jpg'],
            'sports' => ['sports', 'images/page-sports.jpg'],
        ];
    }

    /** @dataProvider pagesWithHeroPhotographs */
    public function test_the_page_renders_its_header_photograph(string $route, string $path): void
    {
        $this->get(route($route))->assertOk()->assertSee($path, escape: false);
    }

    /** @dataProvider pagesWithHeroPhotographs */
    public function test_the_header_photograph_exists_on_disk(string $route, string $path): void
    {
        $this->assertFileExists(public_path($path));
    }

    public function test_every_photograph_the_menu_names_exists(): void
    {
        $images = collect(SiteNav::items())
            ->pluck('image')->filter()->values();

        $this->assertGreaterThanOrEqual(4, $images->count(),
            'the mega menu is meant to carry photographs; none were found');

        foreach ($images as $image) {
            $this->assertFileExists(public_path($image));
        }
    }

    public function test_the_sports_page_shows_its_sporting_photographs(): void
    {
        $this->photo('At the net', 'Sport', 'gallery/volleyball-match.jpg');

        $this->get(route('sports'))->assertOk()
            ->assertSee('A season on the field')
            ->assertSee('gallery/thumbs/volleyball-match.jpg', escape: false);
    }

    /**
     * The strip is data, so it has to vanish rather than leave a headed but
     * empty band — the same guard every other section on the site carries.
     */
    public function test_the_sports_page_drops_the_strip_when_no_photograph_is_categorised(): void
    {
        $this->photo('The library', 'Campus', 'gallery/university-library.jpg');

        $this->get(route('sports'))->assertOk()->assertDontSee('A season on the field');
    }

    public function test_the_home_page_picture_desk_shows_featured_photographs_with_their_captions(): void
    {
        $this->photo('Testing the model', 'Academics', 'gallery/structural-model-test.jpg')
            ->update(['is_featured' => true, 'caption' => 'Students and staff test a paper structural model to destruction.']);
        $this->photo('Not featured', 'Campus', 'gallery/quiet-corner.jpg', 2);

        $this->get(route('home'))->assertOk()
            ->assertSee('The work, as it actually looks')
            ->assertSee('gallery/thumbs/structural-model-test.jpg', escape: false)
            ->assertSee('Students and staff test a paper structural model to destruction.')
            ->assertDontSee('quiet-corner.jpg', escape: false);
    }

    /**
     * A home-page gallery is an editorial choice. With nothing featured the
     * section stays away rather than filling itself with whatever is newest —
     * the same guard every other section on that page carries.
     */
    public function test_the_picture_desk_is_absent_when_nothing_is_featured(): void
    {
        $this->photo('Testing the model', 'Academics', 'gallery/structural-model-test.jpg');

        $this->get(route('home'))->assertOk()->assertDontSee('The work, as it actually looks');
    }

    private function photo(string $title, string $category, string $path, int $sort = 1): GalleryPhoto
    {
        return GalleryPhoto::create([
            'title' => $title, 'alt' => $title, 'category' => $category, 'path' => $path,
            'thumb_path' => str_replace('gallery/', 'gallery/thumbs/', $path),
            'width' => 1600, 'height' => 1065, 'bytes' => 200000,
            'is_published' => true, 'sort_order' => $sort,
        ]);
    }

    /** Campus life promises the campus, so head-and-shoulders portraits stay out of it. */
    public function test_campus_life_leaves_portraits_out_of_the_campus_strip(): void
    {
        $this->photo('A portrait', 'Leadership', 'gallery/a-portrait.jpg', 1);
        $this->photo('The library', 'Campus', 'gallery/university-library.jpg', 2);

        $this->get(route('campus-life'))->assertOk()
            ->assertSee('gallery/thumbs/university-library.jpg', escape: false)
            ->assertDontSee('a-portrait.jpg', escape: false);
    }
}
