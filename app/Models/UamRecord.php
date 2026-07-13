<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UamRecord extends Model
{
    protected $table = 'uam_records';

    protected $fillable = [
        'request_id',
        'module',
        'period',
        'role',
        'description_role',
        'tcode',
        'unit',
        'bpo',
        'access_owner',
        'matrix_data',
        'imported_by',
    ];

    protected $casts = [
        'matrix_data' => 'array',
    ];

    /**
     * The UAM request batch this record belongs to.
     */
    public function uamRequest(): BelongsTo
    {
        return $this->belongsTo(UamRequest::class, 'request_id');
    }

    /**
     * The user who imported this record.
     */
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
