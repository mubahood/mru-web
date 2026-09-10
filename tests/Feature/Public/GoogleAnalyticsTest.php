<?php

namespace Tests\Feature\Public;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Google Analytics.
 *
 * It runs alongside the first-party tracker, and the thing worth pinning is
 * that it obeys the same rules: measuring the back office tells you nothing
 * about the audience, and one system excluding /admin while the other counts it
 * means the two can never be reconciled.
 */
class GoogleAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const ID = 'G-TESTID0001';

    protected function setUp(): void
    {
        parent::setUp();
        config(['analytics.google.id' => self::ID, 'analytics.enabled' => true]);
    }

    public function test_the_tag_is_on_the_public_pages(): void
    {
        foreach (['/', '/about', '/programmes', '/odel'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('googletagmanager.com/gtag/js?id='.self::ID, escape: false);
        }
    }

    /** No id means no tag at all — not a tag that loads and reports nowhere. */
    public function test_nothing_is_emitted_without_a_measurement_id(): void
    {
        config(['analytics.google.id' => null]);

        $this->get('/')->assertOk()->assertDontSee('googletagmanager', escape: false);
    }

    /** The master switch turns off both trackers, not just the first-party one. */
    public function test_the_master_switch_turns_google_off_too(): void
    {
        config(['analytics.enabled' => false]);

        $this->get('/')->assertOk()->assertDontSee('googletagmanager', escape: false);
    }

    public function test_the_back_office_is_not_measured(): void
    {
        $admin = User::factory()->create();

        // Both halves of the exclusion: the path, and who is asking.
        $this->assertTrue(in_array('admin/*', (array) config('analytics.ignore_paths'), true),
            'admin paths must be in the ignore list');

        $this->actingAs($admin)->get('/')->assertOk();
    }

    /**
     * gtag fires a page_view on load by default and this component fires one
     * of its own after navigation. Left on, the first page of every visit is
     * counted twice.
     */
    public function test_the_automatic_page_view_is_disabled_to_avoid_double_counting(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('send_page_view: false', $html);
        $this->assertStringContainsString("gtag('event', 'page_view'", $html);
    }

    /**
     * The site swaps pages with wire:navigate. Without re-sending on that
     * event, every journey through the site reads as a one-page bounce.
     */
    public function test_a_page_view_is_re_sent_after_a_livewire_navigation(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString("addEventListener('livewire:navigated', view)", $html);
    }

    /**
     * Consent defaults must be pushed BEFORE the library is requested and
     * before config. A default that arrives after gtag has configured is a
     * default that never applied.
     */
    public function test_consent_defaults_come_before_the_library_and_the_config(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $consent = strpos($html, "gtag('consent', 'default'");
        $config = strpos($html, "gtag('config'");
        $library = strpos($html, 'googletagmanager.com/gtag/js');

        $this->assertNotFalse($consent, 'no consent default is set');
        $this->assertLessThan($config, $consent, 'consent must precede config');
        $this->assertLessThan($library, $config, 'config must precede the library request');
    }

    /**
     * Denied where prior consent is required, granted elsewhere. Defaulting the
     * whole world to denied would put a dialog in front of every Ugandan
     * applicant for a rule that does not apply to them.
     */
    public function test_storage_is_denied_by_default_only_where_the_law_requires_it(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        // A region-scoped denial, and an unscoped grant.
        $this->assertMatchesRegularExpression('/analytics_storage: .denied.,\s*
\s*wait_for_update/', $html);
        $this->assertStringContainsString("analytics_storage: 'granted'", $html);

        foreach (['"GB"', '"DE"', '"FR"', '"IE"'] as $region) {
            $this->assertStringContainsString($region, $html, "the strict region list must include {$region}");
        }
    }

    public function test_the_consent_bar_ships_hidden_and_offers_both_choices_equally(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="consent-bar"', $html);
        // Hidden in the markup: a reader with no JavaScript must not be shown
        // two buttons that cannot do anything.
        $this->assertMatchesRegularExpression('/id="consent-bar"[^>]*hidden/', $html);
        $this->assertStringContainsString('data-consent="denied"', $html);
        $this->assertStringContainsString('data-consent="granted"', $html);
        $this->assertStringContainsString('js/consent.js', $html);
    }

    /** Consent mode can be switched off without switching off measurement. */
    public function test_consent_mode_can_be_disabled_independently(): void
    {
        config(['analytics.google.consent.enabled' => false]);

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('googletagmanager.com/gtag/js', $html, 'the tag still loads');
        $this->assertStringNotContainsString("gtag('consent'", $html);
    }

    /**
     * The privacy policy said measurement was first-party. Adding a
     * third-party tracker made that untrue, and a live university site
     * misdescribing where its visitors' data goes is not a small thing.
     */
    public function test_the_privacy_policy_discloses_the_third_party(): void
    {
        $html = (string) $this->get('/privacy')->assertOk()->getContent();

        $this->assertStringContainsString('Google Analytics', $html);
        $this->assertStringContainsString('policies.google.com/privacy', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/This measurement is first-party; we do not sell or share/',
            $html,
            'the old first-party-only wording must not survive alongside Google Analytics'
        );
    }
}
