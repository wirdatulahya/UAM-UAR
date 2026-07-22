<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nik',
        'username',
        'email',
        'password',
        'profile_photo_path',
        'role',
        'job_title',
        'position',
        'phone_number',
        'department',
        'division',
        'account_status',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPicAo(): bool
    {
        return $this->role === 'pic_ao';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isAo(): bool
    {
        return $this->role === 'ao';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
