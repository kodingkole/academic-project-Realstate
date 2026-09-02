<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tin_number')) {
                $table->string('tin_number')->nullable()->unique()->after('nid_number');
            }
            if (!Schema::hasColumn('users', 'electricity_bill_no')) {
                $table->string('electricity_bill_no')->nullable()->unique()->after('tin_number');
            }
            if (!Schema::hasColumn('users', 'deed_khatian_no')) {
                $table->string('deed_khatian_no')->nullable()->unique()->after('electricity_bill_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tin_number', 'electricity_bill_no', 'deed_khatian_no']);
        });
    }
};
