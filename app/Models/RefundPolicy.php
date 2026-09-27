<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundPolicy extends Model
{
    protected $fillable = ['refund_window_days', 'human_review_amount', 'active'];
    protected $casts = ['active' => 'boolean', 'human_review_amount' => 'decimal:2'];
}
