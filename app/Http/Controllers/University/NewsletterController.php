<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Support\Spam\FormShield;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The footer newsletter sign-up.
 *
 * Deliberately one field. The subscriber list already carries the addresses
 * migrated from the old site, so this only has to keep adding to it.
 */
class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // A tripped honeypot is answered as success: telling a bot it failed
        // only teaches it to try again.
        if (FormShield::looksAutomated($request->all(), 'newsletter')) {
            return back()->with('newsletter', 'Thank you — you are on the list.');
        }

        $data = $request->validate([
            'email' => 'required|email:rfc|max:191',
        ], [
            'email.required' => 'Enter an email address to subscribe.',
            'email.email' => 'That does not look like an email address.',
        ]);

        // Idempotent: subscribing twice is not an error a visitor should see.
        NewsletterSubscriber::firstOrCreate(['email' => mb_strtolower(trim($data['email']))]);

        return back()->with('newsletter', 'Thank you — you will hear from us.');
    }
}
