<?php

namespace Tests\Feature\University;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Search and social presentation, held to the standard the site was audited
 * against: every public page carries a unique, correctly-sized title and
 * description, a canonical, a share image and valid structured data.
 *
 * The share image is checked by name because it was, until this pass, a
 * medical logo inherited from the platform this codebase was built from —
 * every page shared to WhatsApp or Facebook showed a stethoscope.
 */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    /** Routes that must be presentable to a search engine. */
    public static function publicPages(): array
    {
        return [
            'home' => ['home'], 'about' => ['about'], 'who we are' => ['who-we-are'],
            'governance' => ['governance'], 'contact' => ['contact'],
            'admissions' => ['admissions.index'], 'fees' => ['admissions.fees'],
            'faqs' => ['admissions.faqs'], 'programmes' => ['programmes.index'],
            'faculties' => ['faculties.index'], 'almanac' => ['almanac'],
            'campus life' => ['campus-life'], 'sports' => ['sports'],
            'news' => ['insights.index'], 'scholar' => ['scholar.home'],
        ];
    }

    /** @dataProvider publicPages */
    public function test_a_page_is_presentable_to_a_search_engine(string $routeName): void
    {
        $html = (string) $this->get(route($routeName))->assertOk()->getContent();

        preg_match('#<title>(.*?)</title>#s', $html, $t);
        $title = trim($t[1] ?? '');
        preg_match('#<meta name="description" content="(.*?)"#s', $html, $d);
        $description = trim($d[1] ?? '');

        $this->assertNotSame('', $title, 'every page needs a title');
        $this->assertLessThanOrEqual(62, mb_strlen($title), "title is too long to render whole: {$title}");
        $this->assertNotSame('', $description, 'every page needs a meta description');
        $this->assertStringContainsString('<link rel="canonical"', $html);
        $this->assertStringContainsString('og:image', $html);
    }

    public function test_titles_and_descriptions_are_unique_across_the_site(): void
    {
        $titles = $descriptions = [];

        foreach (self::publicPages() as [$routeName]) {
            $html = (string) $this->get(route($routeName))->getContent();
            preg_match('#<title>(.*?)</title>#s', $html, $t);
            preg_match('#<meta name="description" content="(.*?)"#s', $html, $d);

            $title = trim($t[1] ?? '');
            $description = trim($d[1] ?? '');

            $this->assertNotContains($title, $titles, "duplicate title on {$routeName}: {$title}");
            $this->assertNotContains($description, $descriptions, "duplicate description on {$routeName}");

            $titles[] = $title;
            $descriptions[] = $description;
        }
    }

    public function test_the_share_image_and_site_name_are_the_universitys_own(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('og:site_name" content="Muteesa I Royal University', $html);
        $this->assertStringContainsString('og:locale', $html);
        // Not the inherited platform's branding.
        $this->assertStringNotContainsString('Muhindo', $html);
    }

    public function test_structured_data_is_present_and_parses(): void
    {
        foreach (['home', 'faculties.index', 'admissions.faqs', 'almanac'] as $routeName) {
            $html = (string) $this->get(route($routeName))->getContent();
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

            $this->assertNotEmpty($m[1], "no structured data on {$routeName}");

            foreach ($m[1] as $block) {
                $decoded = json_decode($block, true);
                $this->assertSame(JSON_ERROR_NONE, json_last_error(), "invalid JSON-LD on {$routeName}");
                $this->assertArrayHasKey('@type', $decoded);
            }
        }
    }

    public function test_a_deep_page_carries_a_breadcrumb_trail(): void
    {
        $html = (string) $this->get(route('admissions.fees'))->assertOk()->getContent();

        $this->assertStringContainsString('BreadcrumbList', $html);
        $this->assertStringContainsString('aria-label="Breadcrumb"', $html);
    }
}
