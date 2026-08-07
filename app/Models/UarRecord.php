<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UarRecord extends Model
{
    use HasFactory;

    protected $table = 'uar_records';

    protected $fillable = [
        'uar_session_id',
        'target_module',
        'user_id',
        'full_name',
        'jabatan',
        'user_type',
        'role_name',
        'role_description',
        'role_start_date',
        'role_end_date',
        'tcode',
        'tcode_description',
        'last_logon',
        'system_review_result',
        'system_review_notes',
        'final_review_result',
        'reviewer_notes',
        'is_overridden',
        'is_unmapped_bpo',
    ];

    protected $casts = [
        'is_overridden'   => 'boolean',
        'is_unmapped_bpo' => 'boolean',
    ];

    /**
     * The 6 standard UAR review options.
     */
    public const REVIEW_OPTIONS = [
        'Active' => [
            'label' => 'Active',
            'type'  => 'active',
            'color' => 'success',
            'badge' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
        ],
        'Active - according to assignment/ Exception' => [
            'label' => 'Active - according to assignment/ Exception',
            'type'  => 'active',
            'color' => 'info',
            'badge' => 'bg-sky-500/15 text-sky-700 dark:text-sky-400 border-sky-500/30',
        ],
        'Active - Assigned New Role' => [
            'label' => 'Active - Assigned New Role',
            'type'  => 'active',
            'color' => 'primary',
            'badge' => 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-400 border-indigo-500/30',
        ],
        'Delete - for not logging in > 90 day' => [
            'label' => 'Delete - for not logging in > 90 day',
            'type'  => 'delete',
            'color' => 'danger',
            'badge' => 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30',
        ],
        'Delete - due to mutation and/or promotion/ retirement' => [
            'label' => 'Delete - due to mutation and/or promotion/ retirement',
            'type'  => 'delete',
            'color' => 'warning',
            'badge' => 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border-amber-500/30',
        ],
        'Delete - because it doesn’t match UAM' => [
            'label' => 'Delete - because it doesn’t match UAM',
            'type'  => 'delete',
            'color' => 'danger',
            'badge' => 'bg-red-500/15 text-red-700 dark:text-red-400 border-red-500/30',
        ],
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(UarSession::class, 'uar_session_id');
    }
}
