<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterBpo extends Model
{
    protected $table = 'master_bpos';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Units that belong to this BPO.
     */
    public function units(): HasMany
    {
        return $this->hasMany(MasterUnit::class, 'master_bpo_id');
    }

    /**
     * Scope: only active BPOs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
