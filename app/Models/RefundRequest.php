<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefundRequest extends Model
{
    protected $fillable = ['customer_id', 'order_id', 'message', 'requested_amount', 'ai_intent', 'ai_reason', 'ai_recommendation', 'ai_confidence', 'ai_suspicious', 'ai_missing_information', 'ai_raw_response', 'policy_result', 'final_decision', 'decision_reason', 'escalation_reason', 'status'];
    protected $casts = ['requested_amount' => 'decimal:2', 'ai_confidence' => 'float', 'ai_suspicious' => 'boolean', 'ai_missing_information' => 'array', 'ai_raw_response' => 'array', 'policy_result' => 'array'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function auditLogs(): HasMany { return $this->hasMany(AuditLog::class); }
}
