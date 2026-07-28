<?php

namespace App\Http\Controllers;

use App\Models\MasterBpo;
use App\Models\MasterUnit;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // ─── BPO ──────────────────────────────────────────────────────────────────

    public function bpoIndex()
    {
        $bpos = MasterBpo::orderBy('name')->get();
        return view('master-data.bpo', compact('bpos'));
    }

    public function bpoStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:master_bpos,name',
        ]);

        MasterBpo::create($data);

        return back()->with('success', 'BPO "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function bpoUpdate(Request $request, MasterBpo $bpo)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:master_bpos,name,' . $bpo->id,
            'is_active' => 'boolean',
        ]);

        $bpo->update($data);

        return back()->with('success', 'BPO berhasil diperbarui.');
    }

    public function bpoDestroy(MasterBpo $bpo)
    {
        $bpo->delete();
        return back()->with('success', 'BPO berhasil dihapus.');
    }

    // ─── Unit ─────────────────────────────────────────────────────────────────

    public function unitIndex()
    {
        // Load all BPOs with their units pre-sorted by name.
        // Used for the grouped table (rowspan per BPO) and the Add/Edit dropdowns.
        $bposWithUnits = MasterBpo::orderBy('name')
            ->with(['units' => fn($q) => $q->orderBy('name')])
            ->get();

        // Flat list of BPOs for the Add/Edit modal dropdowns
        $bpos = $bposWithUnits;

        $totalUnits = $bposWithUnits->sum(fn($b) => $b->units->count());

        return view('master-data.unit', compact('bpos', 'bposWithUnits', 'totalUnits'));
    }


    public function unitStore(Request $request)
    {
        $data = $request->validate([
            'master_bpo_id' => 'required|exists:master_bpos,id',
            'name'          => 'required|string|max:100',
        ]);

        $exists = MasterUnit::where('master_bpo_id', $data['master_bpo_id'])
                             ->where('name', $data['name'])
                             ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Unit ini sudah ada di bawah BPO yang dipilih.'])->withInput();
        }

        MasterUnit::create($data);

        return back()->with('success', 'Unit "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function unitUpdate(Request $request, MasterUnit $unit)
    {
        $data = $request->validate([
            'master_bpo_id' => 'required|exists:master_bpos,id',
            'name'          => 'required|string|max:100',
            'is_active'     => 'boolean',
        ]);

        $unit->update($data);

        return back()->with('success', 'Unit berhasil diperbarui.');
    }

    public function unitDestroy(MasterUnit $unit)
    {
        $unit->delete();
        return back()->with('success', 'Unit berhasil dihapus.');
    }

    // ─── JSON API for dropdowns ──────────────────────────────────────────────

    /**
     * Return all active BPOs as JSON (used by Add Role dropdown).
     */
    public function apiBpos()
    {
        $bpos = MasterBpo::active()->orderBy('name')->get(['id', 'name']);
        return response()->json($bpos);
    }

    /**
     * Return active units for a specific BPO as JSON (used by Add Role dropdown).
     */
    public function apiUnits(MasterBpo $bpo)
    {
        $units = $bpo->units()->active()->orderBy('name')->get(['id', 'name']);
        return response()->json($units);
    }

    // ─── Sync from Import ────────────────────────────────────────────────────

    /**
     * Sync BPO + Unit records extracted from an Excel import.
     * 
     * - Creates new BPO records if the name doesn't exist yet.
     * - Creates new Unit records if the BPO + Unit name combo doesn't exist yet.
     * - Never updates or deletes existing records.
     *
     * @param array $bpoBag   List of BPO name strings  ['IT-INFRA', 'NETWORK', ...]
     * @param array $unitBag  List of ['bpo' => '...', 'unit' => '...'] pairs
     */
    public static function syncFromImport(array $bpoBag, array $unitBag): void
    {
        // 1. Upsert BPOs (insert only if name not present)
        foreach (array_unique($bpoBag) as $bpoName) {
            $bpoName = trim($bpoName);
            if ($bpoName === '' || $bpoName === '—') continue;

            MasterBpo::firstOrCreate(['name' => $bpoName]);
        }

        // 2. Upsert Units (insert only if bpo+unit combo not present)
        foreach ($unitBag as $pair) {
            $bpoName  = trim($pair['bpo']  ?? '');
            $unitName = trim($pair['unit'] ?? '');

            if ($bpoName === '' || $bpoName === '—') continue;
            if ($unitName === '' || $unitName === '—') continue;

            $bpo = MasterBpo::where('name', $bpoName)->first();
            if (!$bpo) continue;

            MasterUnit::firstOrCreate([
                'master_bpo_id' => $bpo->id,
                'name'          => $unitName,
            ]);
        }
    }
}
