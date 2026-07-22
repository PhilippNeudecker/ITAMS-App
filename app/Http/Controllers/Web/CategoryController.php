<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    private const MAX_DEPTH = 3;

    public function index(Request $request): Response
    {
        $categories = Category::query()
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return Inertia::render('AssetCategories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AssetCategories/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Category::create($data);

        return redirect()->route('assets.categories.index')->with('success', 'Kategorie erstellt.');
    }

    public function show(Category $category): Response
    {
        return Inertia::render('AssetCategories/Show', [
            'category' => $category,
        ]);
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('AssetCategories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $category->update($data);

        return redirect()->route('assets.categories.index')->with('success', 'Kategorie aktualisiert.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if (Category::where('parent_category_id', $category->id)->exists()) {
            return back()->with('error', "Die Kategorie \"{$category->name}\" hat noch Unterkategorien und kann nicht gelöscht werden.");
        }

        if ($category->assets()->exists()) {
            return back()->with('error', "Die Kategorie \"{$category->name}\" wird noch von Assets verwendet und kann nicht gelöscht werden.");
        }

        $category->delete();

        return redirect()->route('assets.categories.index')->with('success', 'Kategorie gelöscht.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Keine Kategorien ausgewählt.');
        }

        $categories = Category::withCount('assets')->whereIn('id', $ids)->get();

        $withChildren = $categories->filter(
            fn($c) => Category::where('parent_category_id', $c->id)->exists()
        );
        if ($withChildren->isNotEmpty()) {
            return back()->with('error', 'Folgende Kategorien haben noch Unterkategorien und können nicht gelöscht werden: '
                . $withChildren->pluck('name')->implode(', '));
        }

        $withAssets = $categories->filter(fn($c) => $c->assets_count > 0);
        if ($withAssets->isNotEmpty()) {
            return back()->with('error', 'Folgende Kategorien werden noch von Assets verwendet und können nicht gelöscht werden: '
                . $withAssets->pluck('name')->implode(', '));
        }

        Category::whereIn('id', $categories->pluck('id'))->delete();

        return redirect()->route('assets.categories.index')->with('success', $categories->count() . ' Kategorie(n) gelöscht.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'business_code'   => ['required', 'string', 'max:50'],
            'name'             => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($category?->id)->whereNull('deleted_at'),
            ],
            'description'      => ['nullable', 'string'],
            'color'            => ['nullable', 'string', 'max:20'],
            'parent_category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'asset_prefix'     => ['nullable', 'string', 'max:20'],
            'asset_separator'  => ['nullable', 'string', 'max:5'],
            'asset_number_length' => ['nullable', 'integer', 'min:1', 'max:20'],
            'default_warranty_days' => ['nullable', 'integer', 'min:0'],
            'default_warranty_notify_days_before' => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['sometimes', 'boolean'],
        ]);

        if (!empty($data['parent_category_id'])) {
            $this->guardParentAssignment((int) $data['parent_category_id'], $category);
        }

        return $data;
    }

    /**
     * Stellt sicher, dass die gewählte Elternkategorie:
     *  - nicht die Kategorie selbst ist,
     *  - keine ihrer eigenen Unterkategorien (Zyklus) ist,
     *  - und die neue Zuordnung nicht die maximale Tiefe von 3 Ebenen überschreitet
     *    (auch nicht für bereits vorhandene Unter-Kategorien dieser Kategorie).
     */
    private function guardParentAssignment(int $parentId, ?Category $category): void
    {
        if ($category && $parentId === $category->id) {
            throw ValidationException::withMessages([
                'parent_category_id' => 'Eine Kategorie kann nicht ihre eigene Elternkategorie sein.',
            ]);
        }

        if ($category && $this->isDescendantOf($parentId, $category->id)) {
            throw ValidationException::withMessages([
                'parent_category_id' => 'Die gewählte Elternkategorie ist eine Unterkategorie dieser Kategorie.',
            ]);
        }

        $parentDepth = $this->depthOf($parentId);
        $subtreeHeight = $category ? $this->subtreeHeight($category) : 0;

        // depth-index 0..2 sind erlaubt (3 Ebenen). parentDepth + 1 ist die neue
        // Tiefe dieser Kategorie, + subtreeHeight die tiefste bereits vorhandene
        // Unterkategorie darunter.
        if ($parentDepth + 1 + $subtreeHeight > self::MAX_DEPTH - 1) {
            throw ValidationException::withMessages([
                'parent_category_id' => 'Diese Zuordnung würde die maximale Verschachtelungstiefe von ' . self::MAX_DEPTH . ' Ebenen überschreiten.',
            ]);
        }
    }

    private function depthOf(?int $categoryId): int
    {
        $depth = 0;
        $current = $categoryId ? Category::find($categoryId) : null;
        $guard = 0;

        while ($current && $current->parent_category_id && $guard < self::MAX_DEPTH + 2) {
            $depth++;
            $current = Category::find($current->parent_category_id);
            $guard++;
        }

        return $depth;
    }

    private function isDescendantOf(int $possibleDescendantId, int $categoryId): bool
    {
        $current = Category::find($possibleDescendantId);
        $guard = 0;

        while ($current && $current->parent_category_id && $guard < self::MAX_DEPTH + 2) {
            if ((int) $current->parent_category_id === $categoryId) {
                return true;
            }
            $current = Category::find($current->parent_category_id);
            $guard++;
        }

        return false;
    }

    private function subtreeHeight(Category $category, int $guard = 0): int
    {
        if ($guard > self::MAX_DEPTH + 2) {
            return 0;
        }

        $children = Category::where('parent_category_id', $category->id)->get();
        if ($children->isEmpty()) {
            return 0;
        }

        return 1 + $children->max(fn(Category $child) => $this->subtreeHeight($child, $guard + 1));
    }
}
