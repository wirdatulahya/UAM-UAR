<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UamModule extends Model
{
    use HasFactory;

    protected $table = 'uam_modules';

    protected $fillable = [
        'application_slug',
        'code',
        'name',
        'description',
        'icon',
        'status',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
