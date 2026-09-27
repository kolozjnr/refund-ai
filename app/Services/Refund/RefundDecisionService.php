<?php
namespace App\Services\Refund;

class RefundDecisionService
{
    public function decide(array $analysis, array $policy): array
    {
        if ($policy['violations'] !== []) return ['decision' => 'DENIED', 'reason' => implode(' ', $policy['violations']), 'escalation_reason' => null];
        $conflict = ($analysis['suspicious'] ?? false) || ($analysis['conflicting_information'] ?? []) !== [];
        if ($conflict) return ['decision' => 'ESCALATED', 'reason' => 'We noticed information that needs a support specialist to review.', 'escalation_reason' => 'Suspicious or conflicting request information.'];
        if (! empty($policy['requires_human_review'])) return ['decision' => 'ESCALATED', 'reason' => 'A support specialist must review refunds above the policy threshold.', 'escalation_reason' => 'Requested amount exceeds the human-review threshold.'];
        if (($analysis['intent'] ?? 'other') !== 'refund_request' || ($analysis['missing_information'] ?? []) !== [] || ($analysis['confidence'] ?? 0) < 0.55) return ['decision' => 'ESCALATED', 'reason' => 'A support specialist needs more information to safely review this request.', 'escalation_reason' => 'Insufficient or uncertain AI analysis.'];
        if (($analysis['recommendation'] ?? 'escalate') === 'deny') return ['decision' => 'ESCALATED', 'reason' => 'A support specialist will review your request.', 'escalation_reason' => 'AI analysis indicates potential ineligibility; policy remains eligible.'];
        return ['decision' => 'APPROVED', 'reason' => 'Your request meets the refund policy based on the information provided.', 'escalation_reason' => null];
    }
}
