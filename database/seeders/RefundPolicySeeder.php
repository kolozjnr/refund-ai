<?php
namespace Database\Seeders;

use App\Models\RefundPolicy;
use Illuminate\Database\Seeder;

class RefundPolicySeeder extends Seeder
{
    public function run(): void { RefundPolicy::updateOrCreate(['active' => true], ['refund_window_days' => 30, 'human_review_amount' => 500]); }
}
