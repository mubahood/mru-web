<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Programme::with('faculty')->orderBy('sort_order')->orderBy('name');

        if ($level = $request->query('level')) {
            $query->where('level', $level);
        }
        if ($facultyId = $request->query('faculty_id')) {
            $query->where('faculty_id', $facultyId);
        }

        return view('admin.programmes.index', [
            'items' => $query->paginate(30)->withQueryString(),
            'faculties' => Faculty::orderBy('name')->get(),
            'filterLevel' => (string) $level,
            'filterFaculty' => (string) $facultyId,
        ]);
    }

    public function create(): View
    {
        return view('admin.programmes.form', [
            'item' => new Programme,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $programme = Programme::create($this->validated($request));
        $this->storeImage($request, $programme);

        return redirect()->route('admin.programmes.index')->with('success', 'Programme added.');
    }

    public function edit(Programme $programme): View
    {
        return view('admin.programmes.form', [
            'item' => $programme,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Programme $programme): RedirectResponse
    {
        $programme->update($this->validated($request));
        $this->storeImage($request, $programme);

        return redirect()->route('admin.programmes.index')->with('success', 'Programme updated.');
    }

    public function destroy(Programme $programme): RedirectResponse
    {
        if ($programme->image) {
            Storage::disk('public')->delete($programme->image);
        }
        $programme->delete();

        return back()->with('success', 'Programme removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'award_code' => 'nullable|string|max:30',
            'level' => 'required|in:'.implode(',', array_keys(Programme::LEVELS)),
            'duration' => 'nullable|string|max:50',
            'study_modes' => 'nullable|string',
            'tuition_per_semester' => 'nullable|integer|min:0',
            'tuition_currency' => 'nullable|string|max:3',
            'tuition_note' => 'nullable|string|max:255',
            'entry_requirements' => 'nullable|string',
            'description' => 'nullable|string',
            'career_prospects' => 'nullable|string',
            'intake_months' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'sort_order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
        ]);

        $data['study_modes'] = $this->lines($data['study_modes'] ?? null);
        $data['intake_months'] = $this->lines($data['intake_months'] ?? null);
        $data['tuition_currency'] = $data['tuition_currency'] ?? 'UGX';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');

        unset($data['image']);

        return $data;
    }

    /** One entry per textarea line. @return array<int,string> */
    private function lines(?string $text): array
    {
        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $text)
        )));
    }

    private function storeImage(Request $request, Programme $programme): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        if ($programme->image) {
            Storage::disk('public')->delete($programme->image);
        }

        $programme->update(['image' => $request->file('image')->store('programmes', 'public')]);
    }
}
