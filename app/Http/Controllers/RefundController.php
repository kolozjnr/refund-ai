<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreRefundRequest;
use App\Models\Customer;
use App\Models\RefundRequest as RefundRecord;
use App\Services\Refund\RefundService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RefundController extends Controller
{
    public function index(): Response { return Inertia::render('Refunds/Create', ['customers' => Customer::with(['orders' => fn ($q) => $q->with('items')->latest('order_date')])->orderBy('name')->get()]); }
    public function store(StoreRefundRequest $request, RefundService $service): RedirectResponse
    {
        $data = $request->validated();
        $record = $service->queue((int) $data['customer_id'], (int) $data['order_id'], $data['message'], isset($data['requested_amount']) ? (float) $data['requested_amount'] : null);

        return redirect()->route('refunds.show', $record)->with('success', 'Your refund request has been queued for processing.');
    }
    public function show(RefundRecord $refund): Response 
    { 
        $refund->load(['customer', 'order.items']); 
        
        return Inertia::render('Refunds/Result', ['refund' => $refund->only(['id','message','requested_amount','final_decision','decision_reason','escalation_reason','status','created_at']), 'customer' => $refund->customer->only(['name']), 'order' => $refund->order->only(['order_number','currency','total_amount']), 'items' => $refund->order->items->map->only(['product_name','quantity','final_sale'])]); 
    }
}
