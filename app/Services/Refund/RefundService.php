<?php
namespace App\Services\Refund;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\RefundPolicy;
use App\Models\RefundRequest as RefundRecord;
use App\Services\AI\AIProvider;
use App\Jobs\ProcessRefundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefundService
{
    public function __construct(private AIProvider $ai, private RefundPolicyEngine $policyEngine, private RefundDecisionService $decisionEngine) {}

    public function submit(int $customerId, int $orderId, string $message, ?float $requestedAmount = null): RefundRecord
    {
        $record = $this->queue($customerId, $orderId, $message, $requestedAmount);
        $this->process($record);
        return $record->fresh();
    }

    public function queue(int $customerId, int $orderId, string $message, ?float $requestedAmount = null): RefundRecord
    {
        $customer = Customer::findOrFail($customerId);
        $customer->orders()->findOrFail($orderId);

        return DB::transaction(function () use ($customerId, $orderId, $message, $requestedAmount) {
            $record = RefundRecord::create([
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'message' => $message,
                'requested_amount' => $requestedAmount,
                'final_decision' => 'PENDING',
                'decision_reason' => 'Your refund request is queued for processing.',
                'status' => 'queued',
            ]);
            AuditLog::create(['refund_request_id' => $record->id, 'event' => 'refund_request_queued', 'actor_type' => 'system', 'metadata' => []]);
            ProcessRefundRequest::dispatch($record->id)->afterCommit();
            return $record;
        });
    }

    public function process(RefundRecord $record): void
    {
        if ($record->status === 'completed') return;
        $customer = Customer::findOrFail($record->customer_id);
        $order = $customer->orders()->with('items')->findOrFail($record->order_id);
        $policy = RefundPolicy::where('active', true)->firstOrFail();
        $message = $record->message;
        $amountHint = $record->requested_amount === null ? null : (float) $record->requested_amount;

        $context = ['order_number' => $order->order_number, 'order_date' => $order->order_date->toDateString(), 'status' => $order->status, 'total_amount' => (float) $order->total_amount, 'currency' => $order->currency, 'items' => $order->items->map(fn ($item) => ['name' => $item->product_name, 'quantity' => $item->quantity, 'unit_price' => (float) $item->unit_price, 'final_sale' => $item->final_sale])->all(), 'policy' => ['refund_window_days' => $policy->refund_window_days, 'human_review_amount' => (float) $policy->human_review_amount, 'final_sale_refundable' => false]];

        $failed = false;

        try {
            //dd($message, $context);

            $analysis = $this->ai->analyze($message, $context); 
        }
        catch (Throwable $e) { 
            Log::warning('Refund AI analysis failed.', ['exception' => $e::class, 'message' => $e->getMessage()]); $failed = true; $analysis = ['intent' => 'other', 'reason' => 'other', 'requested_amount' => null, 'claims' => [], 'suspicious' => false, 'conflicting_information' => [], 'missing_information' => ['Automated analysis unavailable'], 'recommendation' => 'escalate', 'confidence' => 0, 'customer_response' => 'Your request was received and has been flagged for support review.', 'raw_response' => null]; 
        }

        if ($amountHint !== null) $analysis['requested_amount'] = $amountHint;

        $evaluation = $this->policyEngine->evaluate($order, $analysis, $policy);

        $decision = $this->decisionEngine->decide($analysis, $evaluation);
        

        if ($failed && $decision['decision'] !== 'DENIED') 
        {
            $decision = ['decision' => 'ESCALATED', 'reason' => 'Your request was received and has been flagged for support review.', 'escalation_reason' => 'AI provider unavailable.']; 
        }

        DB::transaction(function () use ($record, $customer, $order, $analysis, $evaluation, $decision, $failed) {
            $record->update(['requested_amount' => $analysis['requested_amount'], 'ai_intent' => $analysis['intent'], 'ai_reason' => $analysis['reason'], 'ai_recommendation' => $analysis['recommendation'], 'ai_confidence' => $analysis['confidence'], 'ai_suspicious' => $analysis['suspicious'], 'ai_missing_information' => $analysis['missing_information'], 'ai_raw_response' => $analysis['raw_response'], 'policy_result' => $evaluation, 'final_decision' => $decision['decision'], 'decision_reason' => $decision['reason'], 'escalation_reason' => $decision['escalation_reason'], 'status' => 'completed']);
            $events = [['customer_order_loaded', ['order_number' => $order->order_number]], ['ai_analysis_started', []], [$failed ? 'ai_analysis_failed' : 'ai_analysis_completed', ['confidence' => $analysis['confidence']]], ['policy_evaluated', ['eligible' => $evaluation['eligible'], 'violations' => $evaluation['violations']]], ['refund_'.strtolower($decision['decision']), ['reason' => $decision['reason']]]];
            foreach ($events as [$event, $metadata]) AuditLog::create(['refund_request_id' => $record->id, 'event' => $event, 'actor_type' => 'system', 'metadata' => $metadata]);
        });
    }
}
