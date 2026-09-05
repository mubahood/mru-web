<?php

namespace Tests\Feature\University;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One codebase serves two products: the university, and the portfolio/product
 * platform it was built from. The second one's commercial pages — a source-code
 * shop with UGX prices, a basket, a checkout and a hire journey — must not be
 * reachable on the university domain, where they are a credibility problem
 * rather than merely irrelevant.
 *
 * The suite as a whole runs with FEATURE_COMMERCE=true (see phpunit.xml)
 * because it also covers that platform. These tests force the flag off and
 * assert the gate actually closes.
 */
class CommerceGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.commerce' => false]);
    }

    public static function commercialUrls(): array
    {
        return [
            'source code index' => ['/source-code'],
            'legacy shop alias' => ['/shop'],
            'legacy projects alias' => ['/projects-for-sale'],
            'basket' => ['/cart'],
            'hire journey' => ['/hire'],
            'legacy project brief' => ['/start-a-project'],
        ];
    }

    /** @dataProvider commercialUrls */
    public function test_commercial_pages_are_not_reachable_on_the_university_site(string $url): void
    {
        $this->get($url)->assertNotFound();
    }

    public function test_the_university_pages_that_link_to_them_still_render(): void
    {
        // The sign-in page and the news index both call route('hire'), and the
        // header calls route('cart.show'). The routes stay *registered* while
        // disabled precisely so URL generation cannot throw — deleting them
        // would take these pages down with them.
        $this->get(route('home'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('insights.index'))->assertOk();
    }

    public function test_the_gate_opens_again_when_the_platform_enables_it(): void
    {
        config(['features.commerce' => true]);

        $this->get('/source-code')->assertOk();
    }
}
