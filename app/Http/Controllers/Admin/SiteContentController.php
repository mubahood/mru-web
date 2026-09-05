<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Editing the website's own words and pictures.
 *
 * Ten settings blobs drive most of the public site and none of them had an
 * editor: changing the homepage headline, the intake notice or a phone number
 * meant editing JSON in the database. Each section is declared in
 * App\Support\SiteContent and rendered from that declaration, so adding a new
 * editable field is a line of schema rather than a new screen.
 */
class SiteContentController extends Controller
{
    private function authorize_(): void
    {
        abort_unless(request()->user()?->can('manage-settings') || request()->user()?->isSuperAdmin(), 403);
    }

    public function index(): View
    {
        $this->authorize_();

        return view('admin.site-content.index', ['sections' => SiteContent::sections()]);
    }

    public function edit(string $section): View
    {
        $this->authorize_();
        $definition = SiteContent::section($section);
        abort_if($definition === null, 404);

        return view('admin.site-content.edit', [
            'key' => $section,
            'section' => $definition,
            'value' => SiteContent::value($section),
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $this->authorize_();
        $definition = SiteContent::section($section);
        abort_if($definition === null, 404);

        $submitted = (array) $request->input('content', []);

        // `lines` fields come back as one textarea and are stored as a list.
        foreach ($definition['fields'] as $name => $field) {
            if (($field['type'] ?? 'text') === 'lines' && ($definition['type'] ?? 'map') === 'map') {
                $submitted[$name] = SiteContent::splitLines($request->input("content.$name"));
            }
        }

        SiteContent::save($section, $submitted);

        return redirect()
            ->route('admin.site-content.edit', $section)
            ->with('success', $definition['label'].' saved. The public site is already showing it.');
    }
}
