<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteNotice;
use App\Support\Notices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The announcement strip above the site header. */
class SiteNoticeController extends Controller
{
    public function index(): View
    {
        return view('admin.notices.index', [
            'items' => SiteNotice::orderBy('sort_order')->orderByDesc('id')->get(),
            'liveIds' => SiteNotice::live()->pluck('id')->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.notices.form', ['item' => new SiteNotice(['template' => 'ticker', 'is_published' => true, 'is_dismissible' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        SiteNotice::create($this->validated($request));
        Notices::forget();

        return redirect()->route('admin.notices.index')->with('success', 'Notice added.');
    }

    public function edit(SiteNotice $notice): View
    {
        return view('admin.notices.form', ['item' => $notice]);
    }

    public function update(Request $request, SiteNotice $notice): RedirectResponse
    {
        $notice->update($this->validated($request));
        Notices::forget();

        return redirect()->route('admin.notices.index')->with('success', 'Notice updated.');
    }

    public function destroy(SiteNotice $notice): RedirectResponse
    {
        $notice->delete();
        Notices::forget();

        return back()->with('success', 'Notice removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'message' => 'required|string|max:300',
            'label' => 'nullable|string|max:60',
            'link_url' => 'nullable|url|max:500',
            'link_label' => 'nullable|string|max:60',
            'template' => 'required|in:'.implode(',', array_keys(SiteNotice::TEMPLATES)),
            'icon' => 'nullable|string|max:40',
            'starts_at' => 'nullable|date',
            // A window that closes before it opens would never show, and the
            // editor would have no way of knowing why.
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'deadline_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ], [
            'ends_at.after_or_equal' => 'The end must not be before the start, or the notice can never appear.',
        ]);

        // A link needs both halves to render at all; say so rather than saving
        // a URL that will silently never be shown.
        if (filled($data['link_url'] ?? null) && blank($data['link_label'] ?? null)) {
            $data['link_label'] = 'Read more';
        }

        $data['is_published'] = $request->boolean('is_published');
        $data['is_dismissible'] = $request->boolean('is_dismissible');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
