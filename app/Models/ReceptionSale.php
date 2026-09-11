<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReceptionSale extends Model
{
    use HasFactory;

    protected $table = 'reception_sales';

    protected $fillable = [
        'tenant_id',
        'sale_number',
        'user_id',
        'cash_register_session_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'booking_id',
        'room_number',
        'payment_type',
        'payment_method',
        'payment_status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_id',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function cashRegisterSession(): BelongsTo
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceptionSaleItem::class);
    }

    public function formattedTotal(): string
    {
        return number_format($this->total_amount / 100, 0, ',', ' ') . ' FCFA';
    }

    public function formattedSubtotal(): string
    {
        return number_format($this->subtotal / 100, 0, ',', ' ') . ' FCFA';
    }

    public function formattedTax(): string
    {
        return number_format($this->tax_amount / 100, 0, ',', ' ') . ' FCFA';
    }

    public function paymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Virement bancaire',
            'room_charge' => 'Débité sur chambre',
            'other' => 'Autre moyen',
            default => $this->payment_method ?? 'Non défini',
        };
    }

    public function paymentStatusLabel(): string
    {
        return match($this->payment_status) {
            'paid' => 'Payé',
            'charged_to_room' => 'Sur Folio (Chambre)',
            'refunded' => 'Remboursé',
            default => $this->payment_status,
        };
    }
}
