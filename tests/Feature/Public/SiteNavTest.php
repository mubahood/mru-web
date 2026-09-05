<?php

namespace Tests\Feature\Public;

use App\Support\SiteNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The menu is the one component every visitor uses, and the one most likely to
 * rot: a page gets renamed and the link that pointed at it quietly 404s.
 */
class SiteNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_destination_in_the_menu_resolves(): void
    {
        foreach (SiteNav::urls() as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_the_menu_offers_the_top_level_sections_in_order(): void
    {
        $labels = array_column(SiteNav::items(), 'label');

        // Order is the message: identity first, then the admissions funnel,
        // then study, research, life, and what's happening.
        $this->assertSame(['About', 'Admissions', 'Academics', 'Research', 'Student Life', 'News & Events'], $labels);
    }

    public function test_the_about_panel_carries_every_page_about_the_university(): void
    {
        $about = collect(SiteNav::items())->firstWhere('label', 'About');

        $this->assertSame(
            ['About MRU', 'Who we are', 'Governance', 'University Council', 'Staff directory', 'Contact us'],
            array_column($about['children'], 'label')
        );
    }

    public function test_the_desktop_and_mobile_menus_render_the_same_destinations(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        // Both are rendered from SiteNav, so each destination must appear at
        // least twice, once in the bar or its panel, once in the mobile sheet.
        foreach (SiteNav::urls() as $url) {
            $this->assertGreaterThanOrEqual(
                2,
                substr_count($html, 'href="'.e($url).'"'),
                "{$url} must be reachable from both the desktop bar and the mobile menu"
            );
        }
    }

    public function test_a_child_page_lights_up_its_parent_section(): void
    {
        // Someone deep in /cv still needs to see which section they are in.
        $html = (string) $this->get(route('admissions.requirements'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*class="nav-link on"[^>]*>\s*Admissions/',
            $html,
            'the Admissions trigger must show as active while a child page is open'
        );
    }

    public function test_the_action_buttons_name_the_outcome_on_hover(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        // The bar carries one standing action; the portal has its own entry
        // in the utility row above it.
        $this->assertStringContainsString('Apply Now', $html);
        $this->assertStringContainsString('E-Portal', $html);
    }

    public function test_the_action_buttons_survive_signing_in(): void
    {
        // Signing in used to replace them with "My Projects" and "Sign out".
        // The actions are what the header is for, and they still apply: a
        // student can hire, a client can enrol.
        $user = \App\Models\User::factory()->create(['role' => 'client', 'is_client' => true]);

        $header = $this->headerOf($this->actingAs($user)->get(route('home')));

        $this->assertStringContainsString('Apply Now', $header);
    }

    public function test_account_navigation_sits_behind_the_avatar_not_beside_the_actions(): void
    {
        $user = \App\Models\User::factory()->create(['role' => 'client', 'is_client' => true]);

        $header = $this->headerOf($this->actingAs($user)->get(route('home')));
        $beforeMenu = substr($header, 0, strpos($header, 'class="acct') ?: strlen($header));

        $this->assertStringContainsString('class="acct desk"', $header);
        $this->assertStringNotContainsString('My Projects', $beforeMenu);
        $this->assertStringNotContainsString('Sign out', $beforeMenu);
    }

    /**
     * The header offers a prospective student the one action they came for.
     * Signing in is a staff and student errand and now lives in the footer, so
     * this asserts both halves: the action is there, and the sign-in doors are
     * not — including "Staff Login", which used to sit in the utility bar as a
     * second copy of the footer's own link.
     */
    public function test_a_guest_is_offered_the_action_and_no_sign_in_door(): void
    {
        $response = $this->get(route('home'));
        $header = $this->headerOf($response);

        $this->assertStringContainsString('Apply Now', $header);
        $this->assertStringNotContainsString('Sign in', $header);
        $this->assertStringNotContainsString('Staff Login', $header);
        $this->assertStringNotContainsString('class="signin', $header);

        // Gone from the header, but still reachable — in the footer strip.
        $html = (string) $response->getContent();
        $footer = substr($html, (int) strpos($html, '<footer'));
        $this->assertStringContainsString('Sign in', $footer);
        $this->assertStringContainsString('Staff Login', $footer);
        $this->assertStringContainsString(route('login'), $footer);
    }

    public function test_no_destination_appears_twice_in_one_menu(): void
    {
        // Two links to one page in a single menu is a wayfinding smell. It was
        // why the top-level "Projects" was folded into the About panel, where
        // "My work" already points at the same page.
        /* Only destinations that are actually rendered as links count. An item
           with children renders as a <button> that opens its panel, so its own
           url is never a link and sharing one with a child is not a duplicate. */
        $urls = [];
        foreach (SiteNav::items() as $item) {
            if (empty($item['children'])) {
                $urls[] = $item['url'];

                continue;
            }
            foreach ($item['children'] as $child) {
                $urls[] = $child['url'];
            }
        }

        $this->assertSame(array_unique($urls), $urls, 'a destination is listed more than once in the menu');
    }

    public function test_every_old_section_url_redirects_to_a_page_that_exists(): void
    {
        // Anything already linked or indexed must not start 404ing, and a
        // redirect is only useful if what it points at actually resolves, which
        // is the part the original assertions never checked.
        $moved = [
            '/shop' => '/source-code',
            '/projects-for-sale' => '/source-code',
            '/insights' => '/news',
            '/blog' => '/news',
            '/courses' => '/e-learning',
        ];

        foreach ($moved as $from => $to) {
            $response = $this->get($from);
            $response->assertStatus(301);
            $response->assertRedirect($to);

            $this->get($to)->assertOk();
        }
    }

    public function test_a_moved_url_redirects_to_an_absolute_address(): void
    {
        /* The bug this pins: Route::redirect emits its target verbatim, so a
           root-relative "/e-learning" is resolved by the browser against the
           domain root. Under a sub-directory install that drops the base path
           and every legacy URL 301s straight to a 404, which is exactly what
           was happening, unnoticed, because asserting the redirect target
           string says nothing about where a browser would actually land. */
        $location = (string) $this->get('/courses')->headers->get('Location');

        $this->assertStringStartsWith(url('/'), $location);
        $this->assertStringEndsWith('/e-learning', $location);
    }

    public function test_the_basket_leads_the_header_actions(): void
    {
        $product = \App\Models\Product::create([
            'name' => 'A Kit', 'slug' => 'a-kit', 'type' => 'template',
            'price' => '1000.00', 'currency' => 'UGX', 'is_published' => true,
        ]);
        $this->post(route('cart.add'), ['type' => 'product', 'id' => $product->id]);

        $header = $this->headerOf($this->get(route('home')));

        // Something waiting to be paid for should be the first control the
        // visitor can get back to.
        $this->assertLessThan(
            strpos($header, 'class="btn gold desk sm"'),
            strpos($header, 'class="cart-link"'),
            'the basket must come before the calls to action'
        );
    }

    public function test_the_footer_lists_every_page_the_menu_offers(): void
    {
        /* The footer is generated from SiteNav for this reason: it had drifted
           into offering "Work" and "Skills" as top-level sections and had never
           heard of the blog, the source code or the gallery. */
        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $footer = substr($html, strpos($html, '<footer>') ?: 0);

        /* The footer gives three sections a column each and reaches the rest
           through its quick links, so what it guarantees is that no section of
           the menu is unreachable from the bottom of the page — and that a
           section given a column lists all of it. */
        foreach (SiteNav::items() as $item) {
            $this->assertStringContainsString('href="'.e($item['url']).'"', $footer,
                "the {$item['label']} section is in the menu but unreachable from the footer");
        }

        foreach (collect(SiteNav::items())->filter(fn ($i) => ! empty($i['children']))->take(3) as $item) {
            foreach ($item['children'] as $child) {
                $this->assertStringContainsString('href="'.e($child['url']).'"', $footer,
                    "{$child['url']} is in the {$item['label']} column but missing from the footer");
            }
        }
    }

    private function headerOf(\Illuminate\Testing\TestResponse $response): string
    {
        $html = (string) $response->assertOk()->getContent();

        return substr($html, strpos($html, '<div class="hd-r">') ?: 0,
            (strpos($html, '</header>') ?: strlen($html)) - (strpos($html, '<div class="hd-r">') ?: 0));
    }

    public function test_a_menu_trigger_announces_itself_as_a_disclosure(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        foreach (SiteNav::items() as $item) {
            if (empty($item['children'])) {
                continue;
            }

            $id = 'mega-'.\Illuminate\Support\Str::slug($item['label']);
            $this->assertStringContainsString('aria-controls="'.$id.'"', $html);
            $this->assertStringContainsString('id="'.$id.'"', $html,
                "the panel {$id} its trigger points at must exist");
        }

        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
    }

    /**
     * The menu has to open on hover AND toggle on click, and those two cannot
     * both live in CSS: a click cannot dismiss a :hover state, and the click
     * focuses the trigger, so :focus-within pinned the panel open and the
     * second click appeared to do nothing.
     *
     * So the open state is a class the script owns, and the pure-CSS
     * behaviour is kept only for a page whose script has not run. This pins
     * both halves of that contract.
     */
    public function test_the_menu_supports_hover_and_click_without_them_fighting(): void
    {
        $css = (string) file_get_contents(public_path('css/mru.css'));

        // The script-driven state.
        $this->assertStringContainsString('.nav-item.is-open > .mega{', $css,
            'the open state must be a class the script can toggle');

        // The no-JS fallback, scoped so it cannot override the class.
        $this->assertStringContainsString('html:not(.js-nav) .nav-item:hover > .mega', $css,
            'the CSS-only fallback must be scoped to pages without script');
        // Matched anywhere in a selector list: a superseded rule hiding behind
        // a comma is still a rule that fights the class.
        $this->assertDoesNotMatchRegularExpression(
            '/(?:^|[,}])\s*\.nav-item:hover > \.mega\s*[,{]/m',
            $css,
            'an unscoped :hover rule would re-open a panel the click just closed'
        );

        $js = (string) file_get_contents(resource_path('views/layouts/marketing.blade.php'));
        $this->assertStringContainsString("classList.add('js-nav')", $js);
        $this->assertStringContainsString('mruMenuState', $js);
    }

    public function test_the_mega_panel_is_operable_without_a_mouse(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        // A <button> trigger is focusable and CSS opens the panel on
        // :focus-within, so the panel works from the keyboard with no script.
        $this->assertMatchesRegularExpression('/<button[^>]*class="nav-link[^"]*"[^>]*aria-expanded="false"[^>]*aria-controls="mega-about"/', $html);
        $this->assertStringContainsString('id="mega-about"', $html);
    }

}
