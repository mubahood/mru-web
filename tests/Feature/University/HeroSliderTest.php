<?php

namespace Tests\Feature\University;

use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The full-height home slider.
 *
 * The behaviour (autoplay, pause, swipe) lives in the browser and is exercised
 * there; what a server-side test can hold still is the contract the behaviour
 * depends on — one active slide, the rest genuinely hidden from both the
 * accessibility tree and the tab order, and exactly one image paying the
 * first-paint cost.
 */
class HeroSliderTest extends TestCase
{
    use RefreshDatabase;

    private function seedSlides(int $count = 3): void
    {
        $slides = [];
        for ($i = 1; $i <= $count; $i++) {
            $slides[] = [
                'image' => "university/hero/hero-{$i}",
                'alt' => "Photograph {$i}",
                'eyebrow' => "Eyebrow {$i}",
                'title' => "Headline {$i}",
                'text' => "Supporting line {$i}.",
                'primary' => ['label' => 'Apply Now', 'url' => 'https://eportal.mru.ac.ug/apply', 'external' => true],
                'secondary' => ['label' => 'Explore Programmes', 'route' => 'programmes.index'],
            ];
        }

        Settings::set('university.hero_slides', json_encode($slides));
    }

    public function test_the_home_page_opens_with_the_slider(): void
    {
        $this->seedSlides();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-hero-slider', false)
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('Headline 1')
            ->assertSee('Headline 3');
    }

    /** The slider's own markup, isolated from the script that drives it. */
    private function sliderMarkup(): string
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $start = strpos($html, '<section class="hero-slider"');

        $this->assertNotFalse($start, 'the home page must render the slider');
        $end = strpos($html, '</section>', $start);

        return substr($html, $start, $end - $start);
    }

    public function test_only_the_first_slide_is_active_and_the_rest_are_hidden(): void
    {
        $this->seedSlides();

        $html = $this->sliderMarkup();

        $this->assertSame(1, substr_count($html, 'hs-slide is-active'),
            'exactly one slide may start active');
        // inert keeps the hidden slides out of the tab order; aria-hidden keeps
        // them out of the accessibility tree. A carousel needs both.
        $this->assertSame(2, substr_count($html, 'aria-hidden="true" inert'));
    }

    public function test_only_the_first_image_pays_the_first_paint_cost(): void
    {
        $this->seedSlides();

        $html = $this->sliderMarkup();

        $this->assertSame(1, substr_count($html, 'loading="eager"'));
        $this->assertSame(1, substr_count($html, 'fetchpriority="high"'));
        $this->assertSame(2, substr_count($html, 'loading="lazy"'));
    }

    public function test_each_slide_offers_the_responsive_image_set(): void
    {
        $this->seedSlides(1);

        $html = $this->sliderMarkup();

        foreach (['-700.jpg 700w', '-1100.jpg 1100w', '-1600.jpg 1600w'] as $entry) {
            $this->assertStringContainsString($entry, $html);
        }
    }

    public function test_there_is_one_dot_per_slide(): void
    {
        $this->seedSlides(3);

        $this->assertSame(3, substr_count($this->sliderMarkup(), 'data-hs-dot'));
    }

    public function test_a_single_slide_needs_no_controls(): void
    {
        $this->seedSlides(1);

        $html = $this->sliderMarkup();

        $this->assertStringNotContainsString('data-hs-dot', $html);
        $this->assertStringNotContainsString('data-hs-next', $html);
    }

    public function test_the_page_degrades_when_no_slides_are_configured(): void
    {
        Settings::set('university.hero_slides', json_encode([]));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('<section class="hero-slider"', false)
            ->assertSee('Ready to join MRU?');
    }

    /**
     * The header floats over the slider, which only works while the CSS keeps
     * a transparent state and the script keeps flagging the page that has one.
     */
    public function test_the_floating_header_contract_holds(): void
    {
        $css = (string) file_get_contents(public_path('css/mru.css'));
        $this->assertStringContainsString('body.has-hero header.site', $css);
        $this->assertStringContainsString('--hdr-crest', $css,
            'the crest has to invert on a photograph, driven by a token');

        $layout = (string) file_get_contents(resource_path('views/layouts/marketing.blade.php'));
        $this->assertStringContainsString('syncHeroFlag', $layout);
        $this->assertStringContainsString('initHeroSlider', $layout);
    }

    /**
     * `App\Support\University` used to keep its own bare static-array cache
     * with no invalidation, layered on top of `Settings`' own (correctly
     * invalidated) cache. The first call in a process pinned it to whatever
     * the settings were at that moment; a write afterwards in the same
     * process was invisible to it. This is exactly that scenario — read,
     * write, read again, all inside one test (one PHP process) — and every
     * test above it in this file already primed the old cache with different
     * slide counts, which is what originally surfaced the bug.
     */
    public function test_settings_written_after_the_first_read_are_not_stale(): void
    {
        $this->seedSlides(3);
        $this->assertCount(3, \App\Support\University::get('hero_slides'));

        $this->seedSlides(1);
        $this->assertCount(1, \App\Support\University::get('hero_slides'),
            'a write after the first read must not be masked by a stale cache');
    }

    public function test_a_page_without_a_slider_keeps_the_solid_header(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee('<section class="hero-slider"', false);
    }
}
