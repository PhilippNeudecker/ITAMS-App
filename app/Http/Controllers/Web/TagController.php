<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(Request $request): Response
    {
        $tags = Tag::when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
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
        return Inertia::render('Tags/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('tags.index')->with('success', 'Tag erstellt.');
    }

    public function show(Tag $tag): Response
    {
        return Inertia::render('Tags/Show', [
            'tag' => $tag,
        ]);
    }

    public function edit(Tag $tag): Response
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        return redirect()->route('tags.show', $tag)->with('success', 'Tag aktualisiert.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();
        return redirect()->route('tags.index')->with('success', 'Tag gelöscht.');
    }
}
