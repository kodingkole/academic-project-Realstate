<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investor_payments', function (Blueprint $table) {
            $table->string('nid_number')->nullable()->after('paid_at');
            $table->string('nid_doc_path')->nullable()->after('nid_number');
            $table->string('tax_cert_no')->nullable()->after('nid_doc_path');
            $table->string('tax_cert_path')->nullable()->after('tax_cert_no');
            $table->string('electricity_bill_no')->nullable()->after('tax_cert_path');
            $table->string('electricity_bill_path')->nullable()->after('electricity_bill_no');
            $table->string('verification_status')->default('verified')->after('electricity_bill_path');
        });
    }

    public function down(): void
    {
        Schema::table('investor_payments', function (Blueprint $table) {
            $table->dropColumn([
                'nid_number',
                'nid_doc_path',
                'tax_cert_no',
                'tax_cert_path',
                'electricity_bill_no',
                'electricity_bill_path',
                'verification_status',
            ]);
        });
    }
};
