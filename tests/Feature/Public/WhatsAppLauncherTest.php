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

    /**
     * The group is staffed by Admissions. A bare green circle said only
     * "WhatsApp" and sent enrolled students into a queue that was never going
     * to answer a fees or results question, so the control now names its own
     * audience — in the visible label and in the accessible name, not in a
     * tooltip, which does not exist on a phone.
     */
    public function test_the_launcher_names_who_it_is_for(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('wa-label', $html, 'the button must carry a visible label');
        $this->assertStringContainsString('Admission enquiries', $html);
        $this->assertMatchesRegularExpression('/aria-label="[^"]*prospective students[^"]*"/', $html,
            'the accessible name must say who the queue is for');
    }

    /** And the pages an enrolled student is most likely to read must send them elsewhere. */
    public function test_pages_an_enrolled_student_reads_point_them_away_from_the_group(): void
    {
        foreach (['/contact', '/odel/support'] as $path) {
            $html = (string) $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('admission enquiries', strtolower($html),
                "{$path} must say the group is for admission enquiries");
            $this->assertMatchesRegularExpression('/already (a|an) (mru )?student/i', $html,
                "{$path} must tell an enrolled student where to go instead");
        }
    }

    /**
     * Joining is a deliberate act.
     *
     * A label on the button is easy to skim past. Every page that can reach the
     * group therefore carries the gate and the script that arms it, so the
     * reader is told what the group is for and has to confirm before the join
     * button will do anything.
     */
    public function test_every_page_that_can_reach_the_group_carries_the_gate(): void
    {
        foreach (['/', '/contact', '/admissions', '/programmes', '/odel', '/odel/support'] as $path) {
            $html = (string) $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('id="wa-gate"', $html, "{$path} has no gate");
            $this->assertStringContainsString('whatsapp-gate.js', $html, "{$path} does not arm the gate");
            $this->assertStringContainsString('data-wg-agree', $html, "{$path} has no confirmation control");
        }
    }

    /** The gate has to say what the group is not for, or it is just a speed bump. */
    public function test_the_gate_names_what_does_not_belong_in_the_group(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('This group is for admission enquiries only', $html);

        // The questions an enrolled student would otherwise bring.
        foreach (['Marks, results and transcripts', 'Registration and course units',
            'Fees statements and balances', 'Examinations and retakes'] as $wrongPlace) {
            $this->assertStringContainsString($wrongPlace, $html);
        }

        // And where those actually go.
        $this->assertStringContainsString('Student E-Portal', $html);
        $this->assertMatchesRegularExpression('/already an mru student/i', $html);
    }

    /**
     * The join control ships disabled. If a template ever rendered it live the
     * confirmation would be decorative, so this pins the served markup rather
     * than trusting the script to switch it off after load.
     */
    public function test_the_join_control_is_disabled_in_the_delivered_markup(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<a[^>]*data-wg-go[^>]*aria-disabled="true"/s',
            $html,
            'the join control must arrive disabled'
        );
        $this->assertMatchesRegularExpression('/<a[^>]*data-wg-go[^>]*tabindex="-1"/s', $html,
            'and out of the tab order until confirmed');
    }

    /**
     * Without JavaScript the gate cannot run, and the links must still work.
     * Breaking the only route to Admissions for someone on a bad connection
     * would cost more than the gate saves.
     */
    public function test_the_links_still_point_at_the_group_without_javascript(): void
    {
        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<a class="wa-btn" href="'.preg_quote(self::GROUP, '/').'"/',
            $html,
            'the launcher must carry a real href, not a placeholder the script fills in'
        );
    }

    /** With the setting emptied the button still goes somewhere real, never to "#". */
    public function test_an_emptied_setting_falls_back_to_the_group(): void
    {
        Settings::set('university.contacts', json_encode(
            array_merge(University::contacts(), ['whatsapp_link' => ''])
        ));

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(self::GROUP, $html);

        // Scoped to the launcher. A blanket ban on href="#" used to stand here
        // and started failing for the right reason: the gate's join control
        // ships with href="#" on purpose, and only takes the real address once
        // the reader has confirmed. What this guards is the launcher falling
        // back to a dead link, which is a different thing.
        $this->assertDoesNotMatchRegularExpression('/<a class="wa-btn"[^>]*href="#"/', $html);
    }
}
