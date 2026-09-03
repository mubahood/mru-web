<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        return view('admin.partners.index', [
            'items' => Partner::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.partners.form', ['item' => new Partner]);
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = Partner::create($this->validated($request));
        $this->storeLogo($request, $partner);

        return redirect()->route('admin.partners.index')->with('success', 'Partner added.');
    }

    public function edit(Partner $partner): View
    {
        return view('admin.partners.form', ['item' => $partner]);
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $partner->update($this->validated($request));
        $this->storeLogo($request, $partner);

        return redirect()->route('admin.partners.index')->with('success', 'Partner updated.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        if ($partner->logo) {
            Storage::disk('public')->delete($partner->logo);
        }
        $partner->delete();

        return back()->with('success', 'Partner removed.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:5120',
            'url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'show_on_home' => 'nullable|boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['show_on_home'] = $request->boolean('show_on_home');

        unset($data['logo']);

        return $data;
    }

    private function storeLogo(Request $request, Partner $partner): void
    {
        if (! $request->hasFile('logo')) {
            return;
        }

        if ($partner->logo) {
            Storage::disk('public')->delete($partner->logo);
        }

        $partner->update(['logo' => $request->file('logo')->store('partners', 'public')]);
    }
}
