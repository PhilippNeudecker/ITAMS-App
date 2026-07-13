<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::with('parent')
            ->withCount('assets')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('AssetCategories/Index', [
            'categories' => $categories,
            'filters'    => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AssetCategories/Create', [
            'parents' => Category::whereNull('parent_category_id')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $data['is_active'] ?? true;

        Tag::create($data);

        return redirect()->route('assets.tags.index')->with('success', 'Tag erstellt.');
    }

    public function show(Category $category): Response
    {
        $category->load(['parent', 'children', 'propertyDefinitions.options']);

        return Inertia::render('Categories/Show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): Response
    {
        $category->load(['parent', 'propertyDefinitions.options']);

        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'parents'  => Category::whereNull('parent_category_id')->where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        return redirect()->route('categories.show', $category)->with('success', 'Kategorie aktualisiert.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategorie gelöscht.');
    }
}
