<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKepalaDesa(): bool
    {
        return $this->role === 'kepala_desa';
    }

    public function isWakilKepalaDesa(): bool
    {
        return $this->role === 'wakil_kepala_desa';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function hasRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
