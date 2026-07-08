<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationTypeDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(Request $request): Response
    {
        $locations = Location::with(['locationType', 'parent'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->type_id, fn($q, $v) => $q->where('location_type_definition_id', $v))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Locations/Index', [
            'locations'     => $locations,
            'locationTypes' => LocationTypeDefinition::orderBy('name')->get(),
            'filters'       => $request->only(['search', 'type_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Locations/Create', [
            'parents'       => Location::whereNull('parent_location_id')->orderBy('name')->get(),
            'locationTypes' => LocationTypeDefinition::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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
            'location'      => $location,
            'parents'       => Location::whereNull('parent_location_id')->where('id', '!=', $location->id)->orderBy('name')->get(),
            'locationTypes' => LocationTypeDefinition::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        return redirect()->route('locations.show', $location)->with('success', 'Standort aktualisiert.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();
        return redirect()->route('locations.index')->with('success', 'Standort gelöscht.');
    }
}
