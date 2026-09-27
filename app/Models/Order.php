<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['customer_id', 'order_number', 'order_date', 'status', 'total_amount', 'currency'];
    protected $casts = ['order_date' => 'date', 'total_amount' => 'decimal:2'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function refundRequests(): HasMany { return $this->hasMany(RefundRequest::class); }
}
