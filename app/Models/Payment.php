<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'status',
        'transaction_id',
        'amount',
        'currency',
        'raw_payload',
        'proof_file_path',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'gateway' => PaymentGateway::class,
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Admin que aprovou/rejeitou o comprovativo (só relevante para gateway manual).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function needsManualReview(): bool
    {
        return $this->gateway === PaymentGateway::Manual
            && $this->status === PaymentStatus::Pending;
    }
}