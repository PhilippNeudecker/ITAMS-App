<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StatusDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StatusDefinitionController extends Controller
{
    public function index(Request $request): Response
    {
        $statusdefinitions = StatusDefinition::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('module')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('StatusDefinitions/Index', [
            'statusdefinitions' => $statusdefinitions,
            'filters'       => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('StatusDefinitions/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        StatusDefinition::create($data);
        return redirect()->route('statusdefinitions.index')->with('success', 'Statusdefinition erstellt.');
    }

    public function show(StatusDefinition $statusdefinition): Response
    {
        return Inertia::render('StatusDefinitions/Show', [
            'statusdefinition' => $statusdefinition,
        ]);
    }

    public function edit(StatusDefinition $statusdefinition): Response
    {
        return Inertia::render('StatusDefinitions/Edit', [
            'statusdefinition' => $statusdefinition,
        ]);
    }

    public function update(Request $request, StatusDefinition $statusdefinition): RedirectResponse
    {
        $data = $this->validated($request, $statusdefinition->id);
        $statusdefinition->update($data);

        return redirect()->route('statusdefinitions.index', $statusdefinition)->with('success', 'Statusdefinition aktualisiert.');
    }

    public function destroy(StatusDefinition $statusdefinition): RedirectResponse
    {
        $statusdefinition->delete();

        return redirect()->route('statusdefinitions.index')->with('success', 'Statusdefinition gelöscht.');
    }

    /**
     * Bulk delete – used by the multi-select toolbar on the StatusDefinitions index page.
     * Refuses the whole batch if any selected StatusDefinition is still assigned to an asset.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Keine Statusdefinition ausgewählt.');
        }

        $statusdefinitions = StatusDefinition::whereIn('id', $ids)->get();

        StatusDefinition::whereIn('id', $statusdefinitions->pluck('id'))->delete();

        return redirect()->route('statusdefinitions.index')->with('success', $statusdefinitions->count() . ' Statusdefinitionen gelöscht.');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            // 'business_code' => ['required', 'string', 'max:50'],
            'name'           => [
                'required', 'string', 'max:255',
                // Rule::unique('statusdefinitions', 'name')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'module' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'color' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean']
        ]);
    }
}
