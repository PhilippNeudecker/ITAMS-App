<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationTypeDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(Request $request): Response
    {
        $locations = Location::with(['locationType', 'parent'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->type_id, fn ($q, $v) => $q->where('location_type_definition_id', $v))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $locations->getCollection()->each->append('full_address');

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'parents' => Location::whereNull('parent_location_id')
                ->orderBy('name')
                ->get(['id', 'name']),
            'locationTypes' => LocationTypeDefinition::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'type_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Locations/Create', [
            'parents' => Location::whereNull('parent_location_id')->orderBy('name')->get(),
            'locationTypes' => LocationTypeDefinition::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Location::create($data);

        return redirect()->route('locations.index')->with('success', 'Standort erstellt.');
    }

    public function show(Location $location): Response
    {
        $location->load(['locationType', 'parent', 'children']);

        return Inertia::render('Locations/Show', [
            'location' => $location,
        ]);
    }

    public function edit(Location $location): Response
    {
        $location->load(['locationType', 'parent']);

        return Inertia::render('Locations/Edit', [
            'location' => $location,
            'parents' => Location::whereNull('parent_location_id')->where('id', '!=', $location->id)->orderBy('name')->get(),
            'locationTypes' => LocationTypeDefinition::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $this->validated($request, $location->id);
        $location->update($data);

        return redirect()->route('locations.show', $location)->with('success', 'Standort aktualisiert.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Standort gelöscht.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Kein Lagerort ausgewählt.');
        }

        $locations = Location::whereIn('id', $ids)->get();

        Location::whereIn('id', $locations->pluck('id'))->delete();

        return redirect()->route('locations.index')->with('success', $locations->count().' Hersteller gelöscht.');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('locations', 'name')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'parent_location_id' => ['nullable', 'uuid'],
            'location_type_definition_id' => ['nullable', 'uuid'],
            'street' => ['nullable', 'string'],
            'house_number' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'additional_info' => ['nullable', 'string'],
        ]);
    }
}
