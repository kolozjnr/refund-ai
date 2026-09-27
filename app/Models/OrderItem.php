<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_name', 'quantity', 'unit_price', 'final_sale', 'item_status'];
    protected $casts = ['final_sale' => 'boolean', 'unit_price' => 'decimal:2'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
