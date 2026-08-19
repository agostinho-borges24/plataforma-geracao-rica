<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_method',
        'whatsapp',
        'country_id',
        'currency',
        'exchange_rate_used',
        'total_base_aoa',
        'total_charged',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_method' => PaymentGateway::class,
        'exchange_rate_used' => 'decimal:8',
        'total_base_aoa' => 'decimal:2',
        'total_charged' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= static::generateOrderNumber();
        });
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Um pedido pode, em teoria, ter mais de uma tentativa de pagamento
     * (ex: cartão falhou, tentou de novo), por isso hasMany.
     * O pagamento "válido" é o mais recente com status completed.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(OrderAccessGrant::class);
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isManualPayment(): bool
    {
        return $this->payment_method === PaymentGateway::Manual;
    }

    /**
     * Se true, o valor foi convertido de AOA para outra moeda (cliente fora de Angola).
     */
    public function wasConverted(): bool
    {
        return $this->currency !== 'AOA' && $this->exchange_rate_used !== null;
    }
}