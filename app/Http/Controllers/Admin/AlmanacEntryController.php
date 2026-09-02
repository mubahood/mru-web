<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlmanacEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlmanacEntryController extends Controller
{
    public function index(): View
    {
        return view('admin.almanac.index', [
            'items' => AlmanacEntry::orderByDesc('academic_year')
                ->orderBy('semester')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.almanac.form', ['item' => new AlmanacEntry]);
    }

    public function store(Request $request): RedirectResponse
    {
        AlmanacEntry::create($this->validated($request));

        return redirect()->route('admin.almanac.index')->with('success', 'Almanac entry added.');
    }

    public function edit(AlmanacEntry $almanacEntry): View
    {
        return view('admin.almanac.form', ['item' => $almanacEntry]);
    }

    public function update(Request $request, AlmanacEntry $almanacEntry): RedirectResponse
    {
        $almanacEntry->update($this->validated($request));

        return redirect()->route('admin.almanac.index')->with('success', 'Almanac entry updated.');
    }

    public function destroy(AlmanacEntry $almanacEntry): RedirectResponse
    {
        $almanacEntry->delete();

        return back()->with('success', 'Almanac entry removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|string|max:40',
            'period' => 'nullable|string|max:80',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'activity' => 'required|string|max:300',
            'sort_order' => 'nullable|integer',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
