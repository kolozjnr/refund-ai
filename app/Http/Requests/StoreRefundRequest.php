<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['customer_id' => ['required', 'integer', 'exists:customers,id'], 'order_id' => ['required', 'integer', 'exists:orders,id'], 'message' => ['required', 'string', 'min:8', 'max:3000'], 'requested_amount' => ['nullable', 'numeric', 'min:0.01', 'max:100000']]; }
}
