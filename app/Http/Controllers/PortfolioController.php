<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

/**
 * What is left of the model app's portfolio controller: the admin messages
 * inbox. The public portfolio pages were replaced by the university site
 * (App\Http\Controllers\University\*); the inbox stays because the contact
 * form now feeds it again.
 */
class PortfolioController extends Controller
{
    public function inbox()
    {
        return view('admin.messages.index', [
            'messages' => ContactMessage::latest()->paginate(20),
        ]);
    }

    public function markRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['read_at' => now()]);

        return back()->with('success', 'Marked as read.');
    }

    /**
     * Delete a message from the inbox.
     *
     * The inbox could only ever grow: a message could be marked read and
     * nothing else, so every piece of spam that got past the shield stayed
     * there permanently.
     */
    public function destroyMessage(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return back()->with('success', 'Message deleted.');
    }
}
