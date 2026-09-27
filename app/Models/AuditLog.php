<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['refund_request_id', 'event', 'actor_type', 'actor_id', 'metadata'];
    protected $casts = ['metadata' => 'array'];
    public function refundRequest(): BelongsTo { return $this->belongsTo(RefundRequest::class); }
}
