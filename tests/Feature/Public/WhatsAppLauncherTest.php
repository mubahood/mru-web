<?php

namespace Tests\Feature\Public;

use App\Support\Settings;
use App\Support\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The WhatsApp launcher.
 *
 * It used to open a panel asking "what brings you here?" and compose an opener
 * through wa.me's ?text= parameter, so the visitor never had to write the
 * awkward first sentence. That design is gone, and these tests changed with
 * it: the destination is now a WhatsApp **group** invite, and a group link
 * carries no prefilled message — ?text= is ignored. A panel that asked a
 * question and then ignored the answer would be worse than no panel.
 *
 * What is worth pinning now is that every WhatsApp control on the site agrees
 * on one destination, that it is the group, and that an editor can change it
 * in one place.
 */
class WhatsAppLauncherTest extends TestCase
{
    use RefreshDatabase;

    private const GROUP = 'https://chat.whatsapp.com/JeS0v2R0UV6Dy4TJCtgj7d';

    public function test_the_constant_is_the_group_invite(): void
    {
        $this->assertSame(self::GROUP, University::WHATSAPP_GROUP);
    }

    public function test_it_is_on_the_public_pages(): void
    {
        foreach (['/', '/about', '/programmes', '/admissions', '/contact'] as $path) {
            $this->get($path)->assertOk()->assertSee(self::GROUP, false);
        }
    }

    /**
     * The whole point of the change: no control anywhere still points at the
     * old one-to-one number.
     */
    public function test_no_page_still_links_to_the_old_direct_number(): void
    {
        foreach (['/', '/about', '/programmes', '/admissions', '/admissions/how-to-apply',
            '/admissions/faqs', '/contact', '/faculties'] as $path) {
            $html = (string) $this->get($path)->assertOk()->getContent();
            $this->assertStringNotContainsString('wa.me/', $html, "wa.me link still on {$path}");
        }
    }

    /** One button, one destination — not a dialog that cannot deliver what it offers. */
    public function test_the_launcher_is_a_single_link_with_no_intent_panel(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('class="wa-btn"', $html);
        $this->assertStringNotContainsString('What brings you here?', $html);
        $this->assertStringNotContainsString('wa-panel', $html);
        $this->assertStringNotContainsString('?text=', $html);
    }

    /** The destination is a setting, so it is changed once and everywhere. */
    public function test_an_editor_can_repoint_every_whatsapp_control_at_once(): void
    {
        $moved = 'https://chat.whatsapp.com/AnotherGroupInviteCode99';

        Settings::set('university.contacts', json_encode(
            array_merge(University::contacts(), ['whatsapp_link' => $moved])
        ));

        foreach (['/', '/admissions', '/programmes'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee($moved, false)
                ->assertDontSee(self::GROUP, false);
        }
    }

    /** With the setting emptied the button still goes somewhere real, never to "#". */
    public function test_an_emptied_setting_falls_back_to_the_group(): void
    {
        Settings::set('university.contacts', json_encode(
            array_merge(University::contacts(), ['whatsapp_link' => ''])
        ));

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(self::GROUP, $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }
}
