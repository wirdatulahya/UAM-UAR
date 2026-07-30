<?php

namespace App\Http\Controllers;

use App\Models\MasterBpo;
use App\Models\MasterUnit;
use App\Models\MasterUser;
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

    // ─── User ─────────────────────────────────────────────────────────────────

    public function userIndex()
    {
        $users = MasterUser::orderBy('name')->get();
        return view('master-data.user', compact('users'));
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:master_users,name',
        ]);

        MasterUser::create($data);

        return back()->with('success', 'User "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function userUpdate(Request $request, MasterUser $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255|unique:master_users,name,' . $user->id,
            'is_active' => 'boolean',
        ]);

        $user->update($data);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function userDestroy(MasterUser $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
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

    /**
     * Return all active Users as JSON.
     */
    public function apiUsers()
    {
        $users = MasterUser::active()->orderBy('name')->get(['id', 'name']);
        return response()->json($users);
    }

    // ─── Sync from Import ────────────────────────────────────────────────────

    /**
     * Sync BPO, Unit, and User records extracted from an Excel import.
     *
     * @param array $bpoBag   List of BPO name strings  ['IT-INFRA', 'NETWORK', ...]
     * @param array $unitBag  List of ['bpo' => '...', 'unit' => '...'] pairs
     * @param array $userBag  List of User names extracted from headers
     */
    public static function syncFromImport(array $bpoBag, array $unitBag, array $userBag = []): void
    {
        // 1. Upsert BPOs
        foreach (array_unique($bpoBag) as $bpoName) {
            $bpoName = trim($bpoName);
            if (!self::isValidMasterName($bpoName)) continue;

            MasterBpo::firstOrCreate(['name' => $bpoName]);
        }

        // 2. Upsert Units
        foreach ($unitBag as $pair) {
            $bpoName  = trim($pair['bpo']  ?? '');
            $unitName = trim($pair['unit'] ?? '');

            if (!self::isValidMasterName($bpoName))  continue;
            if (!self::isValidMasterName($unitName))  continue;

            $bpo = MasterBpo::where('name', $bpoName)->first();
            if (!$bpo) continue;

            MasterUnit::firstOrCreate([
                'master_bpo_id' => $bpo->id,
                'name'          => $unitName,
            ]);
        }
        
        // 3. Upsert Users
        foreach (array_unique($userBag) as $userName) {
            $userName = trim($userName);
            if (!self::isValidMasterName($userName)) continue;
            
            MasterUser::firstOrCreate(['name' => $userName]);
        }
    }

    /**
     * Determine whether a raw string from an Excel cell is a plausible
     * BPO or Unit name (not a batch name, header label, or garbage value).
     *
     * Rules that cause rejection:
     *  - Empty or placeholder values  ('', '—', '-')
     *  - Too long  (> 80 characters — real BPO/Unit names are short)
     *  - Contains known non-BPO keywords (case-insensitive)
     *  - Looks like a UAM batch name  (starts with "UAM_" or contains " V1"/" V2" pattern)
     *  - Looks like a quarter/year period ("Q1 2026", "Q3 2027", …)
     *  - A single word repeated ≥ 3 times  ("SM SM SM SM")
     *  - Purely numeric
     */
    private static function isValidMasterName(string $value): bool
    {
        if ($value === '' || $value === '—' || $value === '-') return false;

        // Too long
        if (mb_strlen($value) > 80) return false;

        // Purely numeric
        if (is_numeric($value)) return false;

        $lower = strtolower($value);

        // Known non-BPO keywords that appear in Excel headers or batch names
        $blacklistKeywords = [
            'user access matrix',
            'access matrix',
            'application owner',
            'access owner',
            'role',
            'tcode',
            'transaction code',
            'description',
            'keterangan',
            'deskripsi',
            'requested by',
            'approved by',
            'accepted by',
            'jabatan',
            'position',
        ];
        foreach ($blacklistKeywords as $kw) {
            if (str_contains($lower, $kw)) return false;
        }

        // Batch-name pattern: starts with "UAM_" or contains " - V1 / V2 / V3"
        if (preg_match('/^uam_/i', $value)) return false;
        if (preg_match('/\bv\d+\b/i', $value) && preg_match('/\bsap\b/i', $value)) return false;

        // Period pattern: "Q1 2026", "Q3 2027", etc.
        if (preg_match('/\bQ[1-4]\s+\d{4}\b/i', $value)) return false;

        // Repeated-word pattern: "SM SM SM SM" — split, unique, if unique count ≤ 2 but total > 2
        $words = preg_split('/\s+/', trim($value));
        if (count($words) >= 3 && count(array_unique($words)) <= 1) return false;

        return true;
    }
}
