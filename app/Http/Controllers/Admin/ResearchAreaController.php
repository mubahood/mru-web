<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResearchArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchAreaController extends Controller
{
    public function index(): View
    {
        return view('admin.research-areas.index', [
            'items' => ResearchArea::withCount('publications')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.research-areas.form', ['item' => new ResearchArea]);
    }

    public function store(Request $request): RedirectResponse
    {
        ResearchArea::create($this->validated($request));

        return redirect()->route('admin.research-areas.index')->with('success', 'Research area added.');
    }

    public function edit(ResearchArea $researchArea): View
    {
        return view('admin.research-areas.form', ['item' => $researchArea]);
    }

    public function update(Request $request, ResearchArea $researchArea): RedirectResponse
    {
        $researchArea->update($this->validated($request));

        return redirect()->route('admin.research-areas.index')->with('success', 'Research area updated.');
    }

    public function destroy(ResearchArea $researchArea): RedirectResponse
    {
        $researchArea->delete();

        return back()->with('success', 'Research area removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'description' => 'nullable|string|max:500',
        ]);
    }
}
