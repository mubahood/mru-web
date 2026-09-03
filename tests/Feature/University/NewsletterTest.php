<?php

namespace Tests\Feature\University;

use App\Models\NewsletterSubscriber;
use App\Support\Spam\FormShield;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_form_is_offered_in_the_footer_of_every_page(): void
    {
        foreach (['/', '/about', '/programmes'] as $path) {
            $html = (string) $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString(route('newsletter.store'), $html);
            $this->assertStringContainsString(FormShield::HONEYPOT, $html,
                "the newsletter form on {$path} must carry the honeypot");
        }
    }

    public function test_an_address_is_added_to_the_list(): void
    {
        $this->from('/')->post(route('newsletter.store'), [
            'email' => 'Nabbosa@Example.com',
            FormShield::HONEYPOT => '',
        ])->assertRedirect('/')->assertSessionHas('newsletter');

        // Stored lower-cased, so the same person cannot arrive twice.
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'nabbosa@example.com']);
    }

    public function test_subscribing_twice_is_not_an_error(): void
    {
        NewsletterSubscriber::create(['email' => 'twice@example.com']);

        $this->from('/')->post(route('newsletter.store'), [
            'email' => 'twice@example.com',
            FormShield::HONEYPOT => '',
        ])->assertRedirect('/')->assertSessionHas('newsletter');

        $this->assertSame(1, NewsletterSubscriber::where('email', 'twice@example.com')->count());
    }

    public function test_a_tripped_honeypot_stores_nothing_but_still_reads_as_success(): void
    {
        $this->from('/')->post(route('newsletter.store'), [
            'email' => 'bot@example.com',
            FormShield::HONEYPOT => 'harvesting',
        ])->assertRedirect('/')->assertSessionHas('newsletter');

        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_a_bad_address_is_reported_rather_than_stored(): void
    {
        $this->from('/')->post(route('newsletter.store'), [
            'email' => 'not-an-address',
            FormShield::HONEYPOT => '',
        ])->assertRedirect('/')->assertSessionHasErrors('email');

        $this->assertSame(0, NewsletterSubscriber::count());
    }
}
