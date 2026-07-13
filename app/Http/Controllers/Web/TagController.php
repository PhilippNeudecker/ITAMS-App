<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(Request $request): Response
    {
        $tags = Tag::query()
            ->withCount('assets')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->boolean('active_only'), fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('AssetTags/Index', [
            'tags'    => $tags,
            'filters' => $request->only(['search', 'active_only']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AssetTags/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $data['is_active'] ?? true;

        Tag::create($data);

        return redirect()->route('assets.tags.index')->with('success', 'Tag erstellt.');
    }

    public function show(Tag $tag): Response
    {
        return Inertia::render('AssetTags/Show', [
            'tag' => $tag,
        ]);
    }

    public function edit(Tag $tag): Response
    {
        return Inertia::render('AssetTags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $this->validated($request, $tag->id);
        $tag->update($data);

        return redirect()->route('assets.tags.index')->with('success', 'Tag aktualisiert.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        if ($tag->assets()->exists()) {
            return back()->with('error', "Der Tag \"{$tag->name}\" wird noch von Assets verwendet und kann nicht gelöscht werden.");
        }

        $tag->delete();

        return redirect()->route('assets.tags.index')->with('success', 'Tag gelöscht.');
    }

    /**
     * Bulk delete – used by the multi-select toolbar on the Tags index page.
     * Refuses the whole batch if any selected tag is still assigned to an asset.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Keine Tags ausgewählt.');
        }

        $tags = Tag::withCount('assets')->whereIn('id', $ids)->get();

        $blocked = $tags->filter(fn($tag) => $tag->assets_count > 0);
        if ($blocked->isNotEmpty()) {
            return back()->with('error', 'Folgende Tags werden noch von Assets verwendet und können nicht gelöscht werden: '
                . $blocked->pluck('name')->implode(', '));
        }

        Tag::whereIn('id', $tags->pluck('id'))->delete();

        return redirect()->route('assets.tags.index')->with('success', $tags->count() . ' Tag(s) gelöscht.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'business_code' => ['required', 'string', 'max:50'],
            'name'           => [
                'required', 'string', 'max:255',
                Rule::unique('tags', 'name')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'color'       => ['nullable', 'string', 'max:20'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);
    }
}
