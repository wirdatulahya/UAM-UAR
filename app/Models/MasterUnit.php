<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterUnit extends Model
{
    protected $table = 'master_units';

    protected $fillable = ['master_bpo_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * BPO that owns this Unit.
     */
    public function bpo(): BelongsTo
    {
        return $this->belongsTo(MasterBpo::class, 'master_bpo_id');
    }

    /**
     * Scope: only active Units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
