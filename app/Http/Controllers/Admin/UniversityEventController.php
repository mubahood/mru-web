<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\UniversityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UniversityEventController extends Controller
{
    public function index(): View
    {
        return view('admin.university-events.index', [
            'items' => UniversityEvent::orderByDesc('starts_at')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.university-events.form', [
            'item' => new UniversityEvent,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $event = UniversityEvent::create($this->validated($request));
        $this->storeImage($request, $event);

        return redirect()->route('admin.university-events.index')->with('success', 'Event added.');
    }

    public function edit(UniversityEvent $universityEvent): View
    {
        return view('admin.university-events.form', [
            'item' => $universityEvent,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, UniversityEvent $universityEvent): RedirectResponse
    {
        $universityEvent->update($this->validated($request));
        $this->storeImage($request, $universityEvent);

        return redirect()->route('admin.university-events.index')->with('success', 'Event updated.');
    }

    public function destroy(UniversityEvent $universityEvent): RedirectResponse
    {
        if ($universityEvent->image) {
            Storage::disk('public')->delete($universityEvent->image);
        }
        $universityEvent->delete();

        return back()->with('success', 'Event removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'excerpt' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'venue' => 'nullable|string|max:255',
            'campus' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'is_published' => 'nullable|boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        unset($data['image']);

        return $data;
    }

    private function storeImage(Request $request, UniversityEvent $event): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->update(['image' => $request->file('image')->store('events', 'public')]);
    }
}
