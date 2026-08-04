<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UarSession extends Model
{
    use HasFactory;

    protected $table = 'uar_sessions';

    protected $fillable = [
        'name',
        'application',
        'module',
        'bpo',
        'period',
        'status',
        'source_type',
        'uploaded_by',
        'total_records',
        'total_active',
        'total_delete',
        'total_overridden',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(UarRecord::class, 'uar_session_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Recalculate and update cached summary counts.
     */
    public function refreshStats(): void
    {
        $this->total_records = $this->records()->count();
        $this->total_active  = $this->records()->where('final_review_result', 'like', 'Active%')->count();
        $this->total_delete  = $this->records()->where('final_review_result', 'like', 'Delete%')->count();
        $this->total_overridden = $this->records()->where('is_overridden', true)->count();
        $this->save();
    }
}
