<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('refund_policies', function (Blueprint $table) { $table->id(); $table->unsignedInteger('refund_window_days')->default(30); $table->decimal('human_review_amount', 10, 2)->default(500); $table->boolean('active')->default(true); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('refund_policies'); }
};
