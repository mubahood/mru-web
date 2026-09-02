<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\StaffMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(): View
    {
        return view('admin.faculties.index', [
            'items' => Faculty::withCount('programmes')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.faculties.form', [
            'item' => new Faculty,
            'staff' => StaffMember::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $faculty = Faculty::create($this->validated($request));
        $this->storeCoverImage($request, $faculty);

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty added.');
    }

    public function edit(Faculty $faculty): View
    {
        return view('admin.faculties.form', [
            'item' => $faculty,
            'staff' => StaffMember::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Faculty $faculty): RedirectResponse
    {
        $faculty->update($this->validated($request));
        $this->storeCoverImage($request, $faculty);

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty updated.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        if ($faculty->cover_image) {
            Storage::disk('public')->delete($faculty->cover_image);
        }
        $faculty->delete();

        return back()->with('success', 'Faculty removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'short_name' => 'nullable|string|max:20',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'about' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'departments' => 'nullable|string',
            'careers' => 'nullable|string',
            'dean_staff_id' => 'nullable|integer|exists:staff_members,id',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'sort_order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
        ]);

        $data['color'] = $data['color'] ?? '#05275C';
        $data['icon'] = $data['icon'] ?? 'fa-building-columns';
        $data['departments'] = $this->lines($data['departments'] ?? null);
        $data['careers'] = $this->lines($data['careers'] ?? null);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');

        unset($data['cover_image']);

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

    private function storeCoverImage(Request $request, Faculty $faculty): void
    {
        if (! $request->hasFile('cover_image')) {
            return;
        }

        if ($faculty->cover_image) {
            Storage::disk('public')->delete($faculty->cover_image);
        }

        $faculty->update(['cover_image' => $request->file('cover_image')->store('faculties', 'public')]);
    }
}
