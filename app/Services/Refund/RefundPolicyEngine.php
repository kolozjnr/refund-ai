<?php
namespace App\Services\Refund;

use App\Models\Order;
use App\Models\RefundPolicy;
use Carbon\Carbon;

class RefundPolicyEngine
{
    public function evaluate(Order $order, array $analysis, RefundPolicy $policy): array
    {
        $reasons = []; $violations = []; $finalSale = $order->items->contains(fn ($item) => $item->final_sale);
        $expired = $order->order_date->copy()->startOfDay()->lt(Carbon::today()->subDays($policy->refund_window_days));
        $validStatus = in_array(strtolower($order->status), ['delivered', 'completed'], true);
        $amount = $analysis['requested_amount'] ?? (float) $order->total_amount;
        if ($finalSale) $violations[] = 'Order contains a final-sale item.'; else $reasons[] = 'Items are not final sale.';
        if ($expired) $violations[] = 'Order is outside the '.$policy->refund_window_days.'-day refund window.'; else $reasons[] = 'Order is within the refund window.';
        if (! $validStatus) $violations[] = 'Order is not in a refundable delivered or completed status.'; else $reasons[] = 'Order status is eligible for review.';
        $amountExceedsOrder = $amount > (float) $order->total_amount;
        if ($amountExceedsOrder) $reasons[] = 'Requested amount exceeds the order total and needs review.';
        if ($amount > (float) $policy->human_review_amount) $reasons[] = 'Requested amount exceeds the human-review threshold.'; else $reasons[] = 'Requested amount is at or below the human-review threshold.';
        if (($analysis['reason'] ?? 'other') === 'damaged_item' || ($analysis['reason'] ?? 'other') === 'incorrect_item') $reasons[] = 'Reported reason may qualify for a refund.';
        return ['eligible' => $violations === [], 'requires_human_review' => $amount > (float) $policy->human_review_amount || $amountExceedsOrder, 'reasons' => $reasons, 'violations' => $violations, 'checks' => ['within_refund_window' => ! $expired, 'not_final_sale' => ! $finalSale, 'valid_order_status' => $validStatus, 'below_review_threshold' => $amount <= (float) $policy->human_review_amount, 'amount_within_order_total' => ! $amountExceedsOrder], 'refund_window_days' => $policy->refund_window_days, 'human_review_amount' => (float) $policy->human_review_amount];
    }
}
