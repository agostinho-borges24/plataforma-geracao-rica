<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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