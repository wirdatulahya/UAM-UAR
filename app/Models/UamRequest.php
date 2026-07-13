<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UamRequest extends Model
{
    protected $table = 'uam_requests';

    protected $fillable = [
        'application',
        'year',
        'period',
        'batch_name',
        'file_name',
        'status',
        'record_count',
        'requested_by',
    ];

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
}
