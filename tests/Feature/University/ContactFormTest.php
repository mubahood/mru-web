<?php

namespace Tests\Feature\University;

use App\Models\ContactMessage;
use App\Support\Spam\FormShield;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** A form_started_at stamp old enough to pass the minimum-fill-time gate. */
    private function humanStamp(): string
    {
        return Crypt::encryptString((string) (now()->getTimestamp() - 30));
    }

    public function test_the_page_renders_with_the_spam_shield_in_place(): void
    {
        $html = (string) $this->get(route('contact'))->assertOk()->getContent();

        $this->assertStringContainsString('form_started_at', $html);
        $this->assertStringContainsString('referral_note', $html, 'the honeypot field must be present');
    }

    public function test_a_real_message_lands_in_the_inbox(): void
    {
        $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'Namono Ruth',
            'email' => 'ruth@example.com',
            'subject' => 'Admission enquiry',
            'message' => 'When does the January intake open?',
            'form_started_at' => $this->humanStamp(),
            'referral_note' => '',
        ])->assertRedirect(route('contact'))->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ruth@example.com',
            'subject' => 'Admission enquiry',
        ]);
    }

    public function test_a_tripped_honeypot_is_answered_as_success_but_stored_nowhere(): void
    {
        $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'spam',
            'message' => 'spam',
            'form_started_at' => $this->humanStamp(),
            'referral_note' => 'I am a robot filling every field',
        ])->assertRedirect(route('contact'))->assertSessionHas('success');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_validation_failures_keep_the_visitor_on_the_form(): void
    {
        $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'N',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
            'form_started_at' => $this->humanStamp(),
            'referral_note' => '',
        ])->assertRedirect(route('contact'))->assertSessionHasErrors(['email', 'subject', 'message']);
    }
}
