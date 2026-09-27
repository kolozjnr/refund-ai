<?php
namespace App\Jobs;

use App\Models\RefundRequest;
use App\Services\Refund\RefundService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRefundRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $refundRequestId) {}

    public function handle(RefundService $service): void
    {
        $service->process(RefundRequest::findOrFail($this->refundRequestId));
    }
}
