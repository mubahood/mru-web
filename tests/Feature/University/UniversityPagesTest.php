<?php

namespace Tests\Feature\University;

use App\Support\SiteNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every destination the public navigation offers must resolve. This is the
 * whole-site smoke test: if a route is renamed or a view breaks, the menu is
 * the first place a visitor meets it.
 */
class UniversityPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_navigation_destination_renders(): void
    {
        foreach (SiteNav::urls() as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_the_homepage_presents_the_university(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Muteesa I Royal University')
            ->assertSee('Apply Now')
            ->assertSee('Rooted in')
            ->assertSee('Ready to join MRU?');
    }

    public function test_the_homepage_carries_organization_json_ld(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $nodes = array_map(fn ($json) => json_decode($json, true), $matches[1]);
        $types = array_column(array_filter($nodes), '@type');

        $this->assertContains('CollegeOrUniversity', $types, 'the homepage must describe the university to search engines');
    }

    public function test_legacy_urls_redirect_to_their_new_homes(): void
    {
        $this->get('/fees')->assertMovedPermanently();
        $this->get('/programs')->assertMovedPermanently();
        $this->get('/jobs')->assertMovedPermanently();
        $this->get('/blog')->assertMovedPermanently();
        $this->get('/mru-scholar')->assertMovedPermanently();
        $this->get('/admission-requirements')->assertMovedPermanently();
    }

    public function test_the_sitemap_lists_university_pages_and_renders(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        $xml = (string) $response->getContent();
        $this->assertStringContainsString('admissions', $xml);
        $this->assertStringContainsString('programmes', $xml);
        $this->assertStringContainsString('scholar', $xml);
    }
}
