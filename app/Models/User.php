<?php

namespace App\Models;

use App\Models\Master\Lembaga;
use App\Traits\HasRolesAndPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, HasRolesAndPermissions;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'no_hp',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
    public function lembaga(): BelongsToMany
    {
        return $this->belongsToMany(Lembaga::class, 'user_lembaga', 'user_id', 'lembaga_id', 'id_user', 'id');
    }

    public function getLembagaIds(): array
    {
        return $this->lembaga()->pluck('lembaga.id')->toArray();
    }

    /** Record guru yang tertaut ke akun ini (dipakai aplikasi mobile guru). */
    public function guru(): HasOne
    {
        return $this->hasOne(\App\Models\Master\Guru::class, 'user_id', 'id_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->lembaga()->count() === 0;
    }

    protected function casts(): array
    {
        return [
            // 'id_user' => 'integer',
            'name' => 'string',
            'username' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'string',
            'status' => 'string',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
