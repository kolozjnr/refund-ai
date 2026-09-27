<?php
namespace App\Http\Controllers;

use App\Models\RefundRequest as RefundRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRefundController extends Controller
{
    public function index(): Response
    {
        $query = RefundRecord::with(['customer','order'])->latest();

        if (request()->filled('decision') && in_array(request('decision'), ['PENDING','APPROVED','DENIED','ESCALATED'], true)) $query->where('final_decision', request('decision'));

        return Inertia::render('Admin/Index', ['summary' => ['total' => RefundRecord::count(), 'approved' => RefundRecord::where('final_decision','APPROVED')->count(), 'denied' => RefundRecord::where('final_decision','DENIED')->count(), 'escalated' => RefundRecord::where('final_decision','ESCALATED')->count()], 'requests' => $query->paginate(15)->withQueryString()->through(fn ($r) => ['id'=>$r->id,'customer'=>$r->customer->name,'order'=>$r->order->order_number,'amount'=>$r->requested_amount,'currency'=>$r->order->currency,'decision'=>$r->status === 'completed' ? $r->final_decision : 'PENDING','created_at'=>$r->created_at->toDateTimeString()]), 'filter' => request('decision')]);
    }
    
    public function show(RefundRecord $refund): Response
    {
        $refund->load(['customer','order.items','auditLogs']);
        return Inertia::render('Admin/Show', ['refund' => $refund, 'logs' => $refund->auditLogs->sortBy('created_at')->values()]);
    }
}
