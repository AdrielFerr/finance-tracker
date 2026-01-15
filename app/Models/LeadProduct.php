<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'name',
        'description',
        'sku',
        'quantity',
        'unit_price',
        'discount',
        'discount_amount',
        'subtotal',
        'total',
        'tax_rate',
        'tax_amount',
        'is_recurring',
        'billing_cycle',
        'order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'order' => 'integer',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Calcular valores automaticamente
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            // Subtotal
            $product->subtotal = $product->quantity * $product->unit_price;
            
            // Desconto
            if ($product->discount > 0) {
                $product->discount_amount = $product->subtotal * ($product->discount / 100);
            }
            
            // Total sem imposto
            $totalBeforeTax = $product->subtotal - $product->discount_amount;
            
            // Imposto
            if ($product->tax_rate > 0) {
                $product->tax_amount = $totalBeforeTax * ($product->tax_rate / 100);
            }
            
            // Total final
            $product->total = $totalBeforeTax + $product->tax_amount;
        });
    }
}