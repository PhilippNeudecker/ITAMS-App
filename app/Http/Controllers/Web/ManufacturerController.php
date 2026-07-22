<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ManufacturerController extends Controller
{
    public function index(Request $request): Response
    {
        $manufacturers = Manufacturer::query()
            ->withCount('assets')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Manufacturers/Index', [
            'manufacturers' => $manufacturers,
            'filters'       => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Manufacturers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Manufacturer::create($data);
        return redirect()->route('assets.manufacturers.index')->with('success', 'Hersteller erstellt.');
    }

    public function show(Manufacturer $manufacturer): Response
    {
        return Inertia::render('Manufacturers/Show', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function edit(Manufacturer $manufacturer): Response
    {
        return Inertia::render('Manufacturers/Edit', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function update(Request $request, Manufacturer $manufacturer): RedirectResponse
    {
        $data = $this->validated($request, $manufacturer->id);
        $manufacturer->update($data);

        return redirect()->route('assets.manufacturers.index', $manufacturer)->with('success', 'Hersteller aktualisiert.');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        if ($manufacturer->assets()->exists()) {
            return back()->with('error', "Der Hersteller \"{$manufacturer->name}\" wird noch von Assets verwendet und kann nicht gelöscht werden.");
        }

        $manufacturer->delete();

        return redirect()->route('assets.manufacturers.index')->with('success', 'Hersteller gelöscht.');
    }

    /**
     * Bulk delete – used by the multi-select toolbar on the Manufacturers index page.
     * Refuses the whole batch if any selected manufacturer is still assigned to an asset.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Keine Hersteller ausgewählt.');
        }

        $manufacturers = Manufacturer::withCount('assets')->whereIn('id', $ids)->get();

        $blocked = $manufacturers->filter(fn($manufacturer) => $manufacturer->assets_count > 0);
        if ($blocked->isNotEmpty()) {
            return back()->with('error', 'Folgende Hersteller werden noch von Assets verwendet und können nicht gelöscht werden: '
                . $blocked->pluck('name')->implode(', '));
        }

        Manufacturer::whereIn('id', $manufacturers->pluck('id'))->delete();

        return redirect()->route('assets.manufacturers.index')->with('success', $manufacturers->count() . ' Hersteller gelöscht.');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            // 'business_code' => ['required', 'string', 'max:50'],
            'name'           => [
                'required', 'string', 'max:255',
                Rule::unique('manufacturers', 'name')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'website' => ['nullable', 'string'],
            'support_contact' => ['nullable', 'string'],
        ]);
    }
}
