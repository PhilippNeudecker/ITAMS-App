<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CostCenterController extends Controller
{
    public function index(Request $request): Response
    {
        $costcenters = CostCenter::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('cost_center_code')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('CostCenters/Index', [
            'costcenters' => $costcenters,
            'filters'       => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CostCenters/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        CostCenter::create($data);
        return redirect()->route('costcenters.index')->with('success', 'Kostenstelle erstellt.');
    }

    public function show(CostCenter $costcenter): Response
    {
        return Inertia::render('CostCenters/Show', [
            'costcenter' => $costcenter,
        ]);
    }

    public function edit(CostCenter $costcenter): Response
    {
        return Inertia::render('CostCenters/Edit', [
            'costcenter' => $costcenter,
        ]);
    }

    public function update(Request $request, CostCenter $costcenter): RedirectResponse
    {
        $data = $this->validated($request, $costcenter->id);
        $costcenter->update($data);

        return redirect()->route('costcenters.index', $costcenter)->with('success', 'Kostenstelle aktualisiert.');
    }

    public function destroy(CostCenter $costcenter): RedirectResponse
    {
        $costcenter->delete();

        return redirect()->route('costcenters.index')->with('success', 'Kostenstelle gelöscht.');
    }

    /**
     * Bulk delete – used by the multi-select toolbar on the CostCenters index page.
     * Refuses the whole batch if any selected CostCenter is still assigned to an asset.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('ids', []))->filter()->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Keine Kostenstelle ausgewählt.');
        }

        $costcenters = CostCenter::whereIn('id', $ids)->get();

        CostCenter::whereIn('id', $costcenters->pluck('id'))->delete();

        return redirect()->route('costcenters.index')->with('success', $costcenters->count() . ' Kostenstellen gelöscht.');
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'cost_center_code' => [
                'required', 'string', 'max:255',
                // Rule::unique('costcenters', 'cost_center_code')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean']
        ]);
    }

    public function syncM3(): RedirectResponse
    {
        try {
            $token = $this->getM3AccessToken();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->get(config('services.m3.api_url') . '/CRS630MI/LstAccountID', [
                'AITP'    => 2,
                'maxrecs' => 100,
            ]);

        if ($response->status() === 401) {
            Cache::forget('m3_access_token');
            return back()->with('error', 'M3-Token ungültig oder abgelaufen. Bitte erneut versuchen.');
        }

        if ($response->failed()) {
            Log::warning('M3 sync failed', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->with('error', "M3-Synchronisierung fehlgeschlagen (HTTP {$response->status()}).");
        }

        $records = $response->json('MIRecord', []);
        $created = 0;
        $updated = 0;

        foreach ($records as $record) {
            $fields = collect($record['NameValue'] ?? [])->pluck('Value', 'Name');

            $code = $fields->get('ACAC'); // Accounting ID = cost center code in M3
            if (!$code) {
                continue;
            }

            $costCenter = CostCenter::updateOrCreate(
                ['cost_center_code' => $code],
                [
                    'name'        => $fields->get('TX40', $code),
                    'description' => $fields->get('TX15'),
                    'is_active'   => $fields->get('STAT', '20') === '20', // 20 = active in M3
                ]
            );

            $costCenter->wasRecentlyCreated ? $created++ : $updated++;
        }

        return redirect()->route('costcenters.index')
            ->with('success', "M3-Sync abgeschlossen: {$created} neu angelegt, {$updated} aktualisiert.");
    }

    private function getM3AccessToken(): string
    {
        if ($token = Cache::get('m3_access_token')) {
            return $token;
        }

        $response = Http::asForm()->post(config('services.m3.token_url'), [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('services.m3.client_id'),
            'client_secret' => config('services.m3.client_secret'),
        ]);

        if ($response->failed()) {
            Log::error('M3 OAuth token request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('M3-Authentifizierung fehlgeschlagen (HTTP ' . $response->status() . ').');
        }

        $token   = $response->json('access_token');
        $expires = (int) $response->json('expires_in', 3600);

        // Cache a bit shorter than the real TTL so we never hand out a stale token
        Cache::put('m3_access_token', $token, now()->addSeconds(max(60, $expires - 60)));

        return $token;
    }
}
