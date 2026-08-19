<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable; // trait fica pronta; feature 2FA desligada em config/fortify.php
use Laravel\Jetstream\HasProfilePhoto;
// use Laravel\Sanctum\HasApiTokens; // descomenta se ativar a feature "api" no config/jetstream.php

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'whatsapp',
        'country_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Atributos calculados incluídos automaticamente na serialização
     * (ex: quando o model User é enviado para o front via Livewire).
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(OrderAccessGrant::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Verifica se o utilizador já tem acesso liberado a um produto
     * (curso ou e-book), independentemente do pedido que gerou o acesso.
     */
    public function hasAccessTo(Product $product): bool
    {
        return $this->accessGrants()
            ->where('product_id', $product->id)
            ->exists();
    }
}