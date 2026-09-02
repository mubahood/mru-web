<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Scholar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ScholarController extends Controller
{
    public function index(): View
    {
        return view('admin.scholars.index', [
            'items' => Scholar::with('faculty')->withCount('publications')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.scholars.form', [
            'item' => new Scholar,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $scholar = Scholar::create($this->validated($request));
        $this->storePhoto($request, $scholar);

        return redirect()->route('admin.scholars.index')->with('success', 'Scholar added.');
    }

    public function edit(Scholar $scholar): View
    {
        return view('admin.scholars.form', [
            'item' => $scholar,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Scholar $scholar): RedirectResponse
    {
        $scholar->update($this->validated($request));
        $this->storePhoto($request, $scholar);

        return redirect()->route('admin.scholars.index')->with('success', 'Scholar updated.');
    }

    public function destroy(Scholar $scholar): RedirectResponse
    {
        if ($scholar->photo) {
            Storage::disk('public')->delete($scholar->photo);
        }
        $scholar->delete();

        return back()->with('success', 'Scholar removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'title' => 'nullable|string|max:100',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'department' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'email' => 'nullable|email|max:255',
            'google_scholar_url' => 'nullable|string|max:255',
            'orcid' => 'nullable|string|max:30',
            'is_published' => 'nullable|boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        unset($data['photo']);

        return $data;
    }

    private function storePhoto(Request $request, Scholar $scholar): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($scholar->photo) {
            Storage::disk('public')->delete($scholar->photo);
        }

        $scholar->update(['photo' => $request->file('photo')->store('scholars', 'public')]);
    }
}
