<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investor_bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('installment_months')->nullable()->after('investment_amount');
            $table->unsignedTinyInteger('installment_day')->nullable()->after('installment_months');
            $table->decimal('monthly_installment_amount', 16, 2)->nullable()->after('installment_day');
            $table->date('next_payment_date')->nullable()->after('monthly_installment_amount');
            $table->unsignedTinyInteger('missed_installments')->default(0)->after('next_payment_date');
            $table->timestamp('forfeited_at')->nullable()->after('missed_installments');
        });

        Schema::table('investor_payments', function (Blueprint $table) {
            $table->string('payment_type')->default('installment')->after('payment_method');
            $table->foreignId('booking_id')->nullable()->after('project_id')->constrained('investor_bookings')->nullOnDelete();
        });

        DB::table('users')->where('name', 'System Administrator')->update(['name' => 'Administrator']);
        DB::table('users')->where('name', 'System Landowner')->update(['name' => 'Landowner']);
        DB::table('users')->where('name', 'System Investor')->update(['name' => 'Investor']);
    }

    public function down(): void
    {
        Schema::table('investor_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn('payment_type');
        });
        Schema::table('investor_bookings', function (Blueprint $table) {
            $table->dropColumn(['installment_months', 'installment_day', 'monthly_installment_amount', 'next_payment_date', 'missed_installments', 'forfeited_at']);
        });
    }
};
