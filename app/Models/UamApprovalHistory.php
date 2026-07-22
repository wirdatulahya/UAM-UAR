<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UamApprovalHistory extends Model
{
    protected $fillable = [
        'uam_request_id',
        'status',
        'approver_name',
        'comment',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function request()
    {
        return $this->belongsTo(UamRequest::class, 'uam_request_id');
    }
}
