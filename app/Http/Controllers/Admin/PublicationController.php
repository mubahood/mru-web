<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\ResearchArea;
use App\Models\Scholar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(): View
    {
        return view('admin.publications.index', [
            'items' => Publication::orderByDesc('year')->orderByDesc('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.publications.form', [
            'item' => new Publication,
            'researchAreas' => ResearchArea::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $publication = Publication::create($this->validated($request));
        $publication->researchAreas()->sync($request->input('research_areas', []));
        $this->syncAuthors($request, $publication);
        $this->storePdf($request, $publication);

        return redirect()->route('admin.publications.index')->with('success', 'Publication added.');
    }

    public function edit(Publication $publication): View
    {
        return view('admin.publications.form', [
            'item' => $publication->load('authorRows.scholar', 'researchAreas'),
            'researchAreas' => ResearchArea::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Publication $publication): RedirectResponse
    {
        $publication->update($this->validated($request));
        $publication->researchAreas()->sync($request->input('research_areas', []));
        $this->syncAuthors($request, $publication);
        $this->storePdf($request, $publication);

        return redirect()->route('admin.publications.index')->with('success', 'Publication updated.');
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        if ($publication->pdf_path) {
            Storage::disk('public')->delete($publication->pdf_path);
        }
        $publication->delete();

        return back()->with('success', 'Publication removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:500',
            'slug' => 'nullable|string|max:220|alpha_dash',
            'abstract' => 'nullable|string',
            'type' => 'required|in:'.implode(',', array_keys(Publication::TYPES)),
            'journal_name' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'volume' => 'nullable|string|max:50',
            'issue' => 'nullable|string|max:50',
            'pages' => 'nullable|string|max:50',
            'publication_date' => 'nullable|date',
            'doi' => 'nullable|string|max:120',
            'url' => 'nullable|string|max:500',
            'pdf' => 'nullable|file|mimes:pdf|max:20480',
            'keywords' => 'nullable|string|max:500',
            'citations' => 'nullable|integer|min:0',
            'status' => 'required|in:'.implode(',', Publication::STATUSES),
            'is_featured' => 'nullable|boolean',
            'research_areas' => 'nullable|array',
            'research_areas.*' => 'integer|exists:research_areas,id',
            'authors' => 'nullable|string',
        ]);

        $data['citations'] = $data['citations'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');

        unset($data['pdf'], $data['research_areas'], $data['authors']);

        return $data;
    }

    /**
     * The form takes one author per line; a line matching an MRU scholar's
     * name links the row, anything else is kept as an external co-author.
     */
    private function syncAuthors(Request $request, Publication $publication): void
    {
        $publication->authorRows()->delete();

        $lines = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $request->input('authors'))
        )));

        foreach ($lines as $order => $name) {
            $scholar = Scholar::where('name', $name)->first();

            $publication->authorRows()->create([
                'scholar_id' => $scholar?->id,
                'external_name' => $scholar ? null : $name,
                'author_order' => $order,
            ]);
        }
    }

    private function storePdf(Request $request, Publication $publication): void
    {
        if (! $request->hasFile('pdf')) {
            return;
        }

        if ($publication->pdf_path) {
            Storage::disk('public')->delete($publication->pdf_path);
        }

        $publication->update(['pdf_path' => $request->file('pdf')->store('scholar/publications', 'public')]);
    }
}
