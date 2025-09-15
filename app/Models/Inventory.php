<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'sku',
        'quantity',
        'min_quantity',
        'unit_price',
        'supplier',
        'supplier_contact',
        'ai_suggestions',
        'needs_reorder',
        'last_restocked',
    ];

    protected $casts = [
        'ai_suggestions' => 'array',
        'needs_reorder' => 'boolean',
        'last_restocked' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeNeedsReorder($query)
    {
        return $query->where('needs_reorder', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_quantity');
    }

    public function checkReorderStatus()
    {
        $this->needs_reorder = $this->quantity <= $this->min_quantity;
        $this->save();
    }
}
