<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'admin' => 'ผู้ดูแลระบบ',
        'accounting' => 'พนักงานบัญชี',
        'sales' => 'พนักงานขาย',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'role',
        'status',
        'password',
    ];

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
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public static function generateUsername(): string
    {
        $last = static::where('username', 'like', 'chilldoi_%')
            ->get()
            ->map(fn ($u) => (int) str_replace('chilldoi_', '', $u->username))
            ->max();

        $next = ($last ?? 0) + 1;

        return 'chilldoi_' . str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    }

    public static function generateRandomPassword(): string
    {
        return Str::password(8, symbols: false);
    }
}
