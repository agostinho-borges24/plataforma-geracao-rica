<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'price',
        'currency',
        'cover_image',
        'active',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Usa o slug nas rotas em vez do id — ex: /produtos/laravel-do-zero
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function accessLinks(): HasMany
    {
        return $this->hasMany(ProductAccessLink::class)->orderBy('position');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeCourses($query)
    {
        return $query->where('type', ProductType::Course);
    }

    public function scopeEbooks($query)
    {
        return $query->where('type', ProductType::Ebook);
    }
}