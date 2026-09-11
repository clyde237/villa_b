<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceptionSaleItem extends Model
{
    use HasFactory;

    protected $table = 'reception_sale_items';

    protected $fillable = [
        'reception_sale_id',
        'service_item_id',
        'category',
        'name',
        'quantity',
        'unit_price',
        'total_price',
        'folio_item_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'integer',
        'total_price' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function receptionSale(): BelongsTo
    {
        return $this->belongsTo(ReceptionSale::class);
    }

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function folioItem(): BelongsTo
    {
        return $this->belongsTo(FolioItem::class);
    }

    public function formattedUnitPrice(): string
    {
        return number_format($this->unit_price / 100, 0, ',', ' ') . ' FCFA';
    }

    public function formattedTotalPrice(): string
    {
        return number_format($this->total_price / 100, 0, ',', ' ') . ' FCFA';
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'breakfast' => 'Petit-déjeuner',
            'laundry' => 'Blanchisserie',
            'spa' => 'Spa & Bien-être',
            'activity' => 'Activité / Loisir',
            'housekeeping' => 'Housekeeping',
            'minibar' => 'Minibar / Boissons',
            default => 'Autre',
        };
    }
}
