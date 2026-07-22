<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LocationTypeDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LocationTypeDefinitionController extends Controller
{
    public function index(Request $request): Response
    {
        $locationtypedefinitions = LocationTypeDefinition::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('LocationTypeDefinitions/Index', [
            'locationtypedefinitions' => $locationtypedefinitions,
            'filters'       => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('LocationTypeDefinitions/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        LocationTypeDefinition::create($data);
        return redirect()->route('locationtypedefinitions.index')->with('success', 'Statusdefinition erstellt.');
    }

    public function show(LocationTypeDefinition $locationtypedefinition): Response
    {
        return Inertia::render('LocationTypeDefinitions/Show', [
            'locationtypedefinition' => $locationtypedefinition,
        ]);
    }

    public function edit(LocationTypeDefinition $locationtypedefinition): Response
    {
        return Inertia::render('LocationTypeDefinitions/Edit', [
            'locationtypedefinition' => $locationtypedefinition,
        ]);
    }

    public function update(Request $request, LocationTypeDefinition $locationtypedefinition): RedirectResponse
    {
        $data = $this->validated($request, $locationtypedefinition->id);
        $locationtypedefinition->update($data);

        return redirect()->route('locationtypedefinitions.index', $locationtypedefinition)->with('success', 'Statusdefinition aktualisiert.');
    }

    public function destroy(LocationTypeDefinition $locationtypedefinition): RedirectResponse
    {
        $locationtypedefinition->delete();

        return redirect()->route('locationtypedefinitions.index')->with('success', 'Statusdefinition gelöscht.');
    }

    /**
     * Bulk delete – used by the multi-select toolbar on the LocationTypeDefinitions index page.
     * Refuses the whole batch if any selected LocationTypeDefinition is still assigned to an asset.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Keine Statusdefinition ausgewählt.');
        }

        $locationtypedefinitions = LocationTypeDefinition::whereIn('id', $ids)->get();

        LocationTypeDefinition::whereIn('id', $locationtypedefinitions->pluck('id'))->delete();

        return redirect()->route('locationtypedefinitions.index')->with('success', $locationtypedefinitions->count() . ' Statusdefinitionen gelöscht.');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            // 'business_code' => ['required', 'string', 'max:50'],
            'name'           => [
                'required', 'string', 'max:255',
                // Rule::unique('locationtypedefinitions', 'name')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean']
        ]);
    }
}
