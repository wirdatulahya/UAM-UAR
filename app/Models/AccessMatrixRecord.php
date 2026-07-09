<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessMatrixRecord extends Model
{
    protected $fillable = [
        'no',
        'nip',
        'nama',
        'jabatan',
        'department',
        'aplikasi',
        'hak_akses',
        'status',
        'keterangan',
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
