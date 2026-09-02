<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investor_bookings', function (Blueprint $table) {
            $table->dropUnique('investor_bookings_user_id_project_id_unit_no_unique');
            $table->unique(['project_id', 'unit_no']);
        });

        Schema::table('investor_payments', function (Blueprint $table) {
            $table->string('payer_reference')->nullable()->after('transaction_id');
            $table->string('gateway_transaction_id')->nullable()->after('payer_reference');
            $table->foreignId('reviewed_by')->nullable()->after('gateway_transaction_id')->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable()->after('reviewed_by');
            $table->timestamp('paid_at')->nullable()->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('investor_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['payer_reference', 'gateway_transaction_id', 'review_note', 'paid_at']);
        });
        Schema::table('investor_bookings', function (Blueprint $table) {
            $table->dropUnique('investor_bookings_project_id_unit_no_unique');
            $table->unique(['user_id', 'project_id', 'unit_no']);
        });
    }
};
