<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManufacturerController extends Controller
{
    public function index(Request $request): Response
    {
        $manufacturers = Manufacturer::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
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
        return redirect()->route('manufacturers.index')->with('success', 'Hersteller erstellt.');
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
        return redirect()->route('manufacturers.show', $manufacturer)->with('success', 'Hersteller aktualisiert.');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        $manufacturer->delete();
        return redirect()->route('manufacturers.index')->with('success', 'Hersteller gelöscht.');
    }
}
