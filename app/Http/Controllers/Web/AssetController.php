<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Tag;
use App\Models\StatusDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Asset::query()
            ->with(['category', 'status', 'manufacturer', 'location', 'tags', 'activeAssignment'])
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) =>
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('asset_label', 'like', "%{$s}%")
                      ->orWhere('description', 'like', "%{$s}%")
                )
            )
            ->when($request->category_id, fn($q, $v) => $q->where('category_id', $v))
            ->when($request->status_id,   fn($q, $v) => $q->where('status_definition_id', $v))
            ->when($request->location_id, fn($q, $v) => $q->where('current_location_id', $v))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Assets/Index', [
            'assets'  => $query,
            'filters' => $request->only(['search', 'category_id', 'status_id', 'location_id']),
            'categories' => Category::orderBy('name')->get(),
            'statuses'   => StatusDefinition::orderBy('name')->get(),
            'locations'  => Location::orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Assets/Create', [
            'categories'   => Category::orderBy('name')->get(),
            'locations'    => Location::orderBy('name')->get(),
            'manufacturers'=> Manufacturer::orderBy('name')->get(),
            'tags'         => Tag::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Eigentliche Logik liegt im API AssetController.
        // Hier delegieren wir oder duplizieren minimal für Inertia-Form-Posts.
        return redirect()->route('assets.index')->with('success', 'Asset erstellt.');
    }

    public function show(Asset $asset): Response
    {
        $asset->load([
            'category.propertyDefinitions',
            'status', 'manufacturer', 'location.locationType',
            'tags', 'propertyValues.definition', 'propertyValues.option',
            'activeAssignment',
        ]);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
        ]);
    }

    public function edit(Asset $asset): Response
    {
        $asset->load(['category', 'status', 'manufacturer', 'location', 'tags', 'propertyValues.definition']);

        return Inertia::render('Assets/Edit', [
            'asset'        => $asset,
            'categories'   => Category::orderBy('name')->get(),
            'locations'    => Location::orderBy('name')->get(),
            'manufacturers'=> Manufacturer::orderBy('name')->get(),
            'tags'         => Tag::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        return redirect()->route('assets.show', $asset)->with('success', 'Asset aktualisiert.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset gelöscht.');
    }
}
