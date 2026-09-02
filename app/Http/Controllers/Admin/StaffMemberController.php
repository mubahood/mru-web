<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\StaffMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StaffMemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = StaffMember::with('faculty')->orderBy('sort_order')->orderBy('name');

        if ($role = $request->query('staff_role')) {
            $query->where('staff_role', $role);
        }

        return view('admin.staff-members.index', [
            'items' => $query->paginate(30)->withQueryString(),
            'filterRole' => (string) $role,
        ]);
    }

    public function create(): View
    {
        return view('admin.staff-members.form', [
            'item' => new StaffMember,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $staffMember = StaffMember::create($this->validated($request));
        $this->storePhoto($request, $staffMember);

        return redirect()->route('admin.staff-members.index')->with('success', 'Staff member added.');
    }

    public function edit(StaffMember $staffMember): View
    {
        return view('admin.staff-members.form', [
            'item' => $staffMember,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StaffMember $staffMember): RedirectResponse
    {
        $staffMember->update($this->validated($request));
        $this->storePhoto($request, $staffMember);

        return redirect()->route('admin.staff-members.index')->with('success', 'Staff member updated.');
    }

    public function destroy(StaffMember $staffMember): RedirectResponse
    {
        if ($staffMember->photo) {
            Storage::disk('public')->delete($staffMember->photo);
        }
        $staffMember->delete();

        return back()->with('success', 'Staff member removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'title' => 'nullable|string|max:255',
            'staff_role' => 'required|in:'.implode(',', array_keys(StaffMember::ROLES)),
            'group_label' => 'nullable|string|max:100',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'department' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'sort_order' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');

        unset($data['photo']);

        return $data;
    }

    private function storePhoto(Request $request, StaffMember $staffMember): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($staffMember->photo) {
            Storage::disk('public')->delete($staffMember->photo);
        }

        $staffMember->update(['photo' => $request->file('photo')->store('staff', 'public')]);
    }
}
