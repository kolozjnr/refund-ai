<?php
namespace Tests\Unit;

use App\Models\Order;
use App\Models\RefundPolicy;
use App\Services\Refund\RefundDecisionService;
use App\Services\Refund\RefundPolicyEngine;
use Carbon\Carbon;
use Tests\TestCase;

class RefundDecisionServiceTest extends TestCase
{
    private function evaluate(array $overrides = [], array $analysisOverrides = []): array
    {
        Carbon::setTestNow('2026-09-25');
        $order = new Order(['order_date'=>'2026-09-22','status'=>'delivered','total_amount'=>200]);
        $order->setRelation('items', collect([(object)['final_sale'=>false]]));
        foreach ($overrides as $key=>$value) { if ($key === 'final_sale') $order->setRelation('items', collect([(object)['final_sale'=>true]])); else $order->{$key}=$value; }
        $policy = new RefundPolicy(['refund_window_days'=>30,'human_review_amount'=>500]);
        $analysis = array_merge(['intent'=>'refund_request','reason'=>'damaged_item','requested_amount'=>200,'suspicious'=>false,'conflicting_information'=>[],'missing_information'=>[],'confidence'=>0.9,'recommendation'=>'approve'], $analysisOverrides);
        $policyResult = (new RefundPolicyEngine)->evaluate($order,$analysis,$policy);
        return [(new RefundDecisionService)->decide($analysis,$policyResult),$policyResult];
    }
    public function test_eligible_damaged_or_incorrect_item_is_approved(): void { $this->assertSame('APPROVED',$this->evaluate()[0]['decision']); $this->assertSame('APPROVED',$this->evaluate([],['reason'=>'incorrect_item'])[0]['decision']); }
    public function test_final_sale_and_expired_orders_are_denied(): void { $this->assertSame('DENIED',$this->evaluate(['final_sale'=>true])[0]['decision']); $this->assertSame('DENIED',$this->evaluate(['order_date'=>'2026-07-01'])[0]['decision']); }
    public function test_large_amount_suspicious_and_conflict_escalate(): void { $this->assertSame('ESCALATED',$this->evaluate(['total_amount'=>1000],['requested_amount'=>550])[0]['decision']); $this->assertSame('ESCALATED',$this->evaluate([],['suspicious'=>true])[0]['decision']); $this->assertSame('ESCALATED',$this->evaluate([],['conflicting_information'=>['status mismatch']])[0]['decision']); }
    public function test_order_claim_conflict_denies_from_authoritative_status(): void { $this->assertSame('DENIED',$this->evaluate(['status'=>'cancelled'])[0]['decision']); }
    public function test_ai_failure_escalates_without_approving(): void { $analysis=['intent'=>'other','missing_information'=>['provider unavailable'],'confidence'=>0,'recommendation'=>'escalate']; $this->assertSame('ESCALATED',(new RefundDecisionService)->decide($analysis,['violations'=>[],'requires_human_review'=>false])['decision']); }
}
