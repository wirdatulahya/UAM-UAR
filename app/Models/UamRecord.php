<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UamRecord extends Model
{
    protected $table = 'uam_records';

    protected $fillable = [
        'role',
        'description_role',
        'tcode',
        'uni',
        'bpo',
        'access_owner',
        'imported_by',
    ];

    /**
     * The user who imported this record.
     */
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
