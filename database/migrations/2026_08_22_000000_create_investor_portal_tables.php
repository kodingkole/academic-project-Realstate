<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('investor_bookings', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('unit_no'); $table->decimal('investment_amount', 16, 2); $table->string('status')->default('reserved'); $table->timestamps();
            $table->unique(['user_id','project_id','unit_no']);
        });
        Schema::create('investor_payments', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 16, 2); $table->string('payment_method'); $table->string('status')->default('pending'); $table->unsignedInteger('installment_no')->nullable(); $table->string('transaction_id')->nullable(); $table->timestamps();
        });
        Schema::create('investor_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->string('doc_type'); $table->string('file_path')->nullable(); $table->date('issued_at')->nullable(); $table->timestamps();
        });
        Schema::create('investor_notifications', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->text('message'); $table->string('type')->default('update'); $table->boolean('is_read')->default(false); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('investor_notifications'); Schema::dropIfExists('investor_documents'); Schema::dropIfExists('investor_payments'); Schema::dropIfExists('investor_bookings'); }
};
