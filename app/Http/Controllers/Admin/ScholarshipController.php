<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(): View
    {
        return view('admin.scholarships.index', [
            'items' => Scholarship::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.scholarships.form', ['item' => new Scholarship]);
    }

    public function store(Request $request): RedirectResponse
    {
        Scholarship::create($this->validated($request));

        return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship added.');
    }

    public function edit(Scholarship $scholarship): View
    {
        return view('admin.scholarships.form', ['item' => $scholarship]);
    }

    public function update(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $scholarship->update($this->validated($request));

        return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship updated.');
    }

    public function destroy(Scholarship $scholarship): RedirectResponse
    {
        $scholarship->delete();

        return back()->with('success', 'Scholarship removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'category' => 'nullable|string|max:50',
            'coverage' => 'nullable|string|max:120',
            'criteria' => 'nullable|string',
            'amount_note' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
