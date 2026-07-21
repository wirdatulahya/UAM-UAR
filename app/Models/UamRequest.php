<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UamRequest extends Model
{
    protected $table = 'uam_requests';

    protected $fillable = [
        'parent_id',
        'group_id',
        'application',
        'module',
        'year',
        'period',
        'version',
        'batch_name',
        'file_name',
        'status',
        'approver_comment',
        'ao',
        'record_count',
        'requested_by',
        'requester_nik',
        'signed_by',
        'global_matrix',
    ];

    protected $casts = [
        'global_matrix' => 'array',
    ];

    /**
     * Get the fully formatted period including version if available.
     */
    public function getFullPeriodAttribute()
    {
        $base = "{$this->period} {$this->year}";
        if (!empty($this->version)) {
            return "{$base} - {$this->version}";
        }
        return $base;
    }

    /**
     * The user who created this request.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * All UAM records that belong to this request batch.
     */
    public function records(): HasMany
    {
        return $this->hasMany(UamRecord::class, 'request_id');
    }

    /**
     * All approval history records for this batch.
     */
    public function approvalHistories(): HasMany
    {
        return $this->hasMany(UamApprovalHistory::class, 'uam_request_id')->orderBy('created_at', 'desc');
    }

    /**
     * The parent version of this request.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The child versions of this request.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Check if this request is the latest version.
     */
    public function isLatestVersion(): bool
    {
        return !$this->children()->exists();
    }
}
