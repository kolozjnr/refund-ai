<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email')->unique(); $table->string('phone')->nullable(); $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_id')->constrained()->cascadeOnDelete(); $table->string('order_number')->unique(); $table->date('order_date'); $table->string('status'); $table->decimal('total_amount', 10, 2); $table->string('currency', 3)->default('USD'); $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->string('product_name'); $table->unsignedInteger('quantity'); $table->decimal('unit_price', 10, 2); $table->boolean('final_sale')->default(false); $table->string('item_status')->nullable(); $table->timestamps(); $table->unique(['order_id', 'product_name']);
        });
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_id')->constrained(); $table->foreignId('order_id')->constrained(); $table->text('message'); $table->decimal('requested_amount', 10, 2)->nullable();
            $table->string('ai_intent')->nullable(); $table->string('ai_reason')->nullable(); $table->string('ai_recommendation')->nullable(); $table->decimal('ai_confidence', 4, 3)->nullable(); $table->boolean('ai_suspicious')->default(false);
            $table->jsonb('ai_missing_information')->nullable(); $table->jsonb('ai_raw_response')->nullable(); $table->jsonb('policy_result')->nullable(); $table->string('final_decision'); $table->text('decision_reason')->nullable(); $table->text('escalation_reason')->nullable(); $table->string('status')->default('completed'); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('refund_request_id')->nullable()->constrained()->nullOnDelete(); $table->string('event'); $table->string('actor_type'); $table->unsignedBigInteger('actor_id')->nullable(); $table->jsonb('metadata')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs'); Schema::dropIfExists('refund_requests'); Schema::dropIfExists('order_items'); Schema::dropIfExists('orders'); Schema::dropIfExists('customers');
    }
};
