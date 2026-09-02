<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function index(): View
    {
        return view('admin.vacancies.index', [
            'items' => Vacancy::orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vacancies.form', ['item' => new Vacancy]);
    }

    public function store(Request $request): RedirectResponse
    {
        $vacancy = Vacancy::create($this->validated($request));
        $this->storeAttachment($request, $vacancy);

        return redirect()->route('admin.vacancies.index')->with('success', 'Vacancy added.');
    }

    public function edit(Vacancy $vacancy): View
    {
        return view('admin.vacancies.form', ['item' => $vacancy]);
    }

    public function update(Request $request, Vacancy $vacancy): RedirectResponse
    {
        $vacancy->update($this->validated($request));
        $this->storeAttachment($request, $vacancy);

        return redirect()->route('admin.vacancies.index')->with('success', 'Vacancy updated.');
    }

    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->attachment) {
            Storage::disk('public')->delete($vacancy->attachment);
        }
        $vacancy->delete();

        return back()->with('success', 'Vacancy removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'reference_no' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:40',
            'location' => 'nullable|string|max:80',
            'deadline_on' => 'nullable|date',
            'summary' => 'nullable|string',
            'requirements' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        unset($data['attachment']);

        return $data;
    }

    private function storeAttachment(Request $request, Vacancy $vacancy): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($vacancy->attachment) {
            Storage::disk('public')->delete($vacancy->attachment);
        }

        $vacancy->update(['attachment' => $request->file('attachment')->store('vacancies', 'public')]);
    }
}
