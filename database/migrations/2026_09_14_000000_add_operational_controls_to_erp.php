<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_material_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('planned_quantity', 12, 2)->default(0);
            $table->decimal('daily_quantity', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'material_id']);
        });
        Schema::create('equipment_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours_used', 10, 2);
            $table->decimal('hourly_rate', 12, 2);
            $table->date('usage_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->decimal('estimated_cost', 16, 2)->default(0)->after('quantity');
            $table->string('finance_status')->default('pending')->after('status');
            $table->foreignId('finance_approved_by')->nullable()->after('finance_status')->constrained('users')->nullOnDelete();
            $table->text('finance_note')->nullable()->after('finance_approved_by');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('hourly_rate', 12, 2)->default(0)->after('status');
            $table->decimal('work_hours', 8, 2)->default(8)->after('hourly_rate');
        });
        Schema::table('equipment', function (Blueprint $table) {
            $table->decimal('hourly_rate', 12, 2)->default(0)->after('total_hours_logged');
        });
    }
    public function down(): void
    {
        Schema::table('equipment', fn (Blueprint $table) => $table->dropColumn('hourly_rate'));
        Schema::table('attendances', fn (Blueprint $table) => $table->dropColumn(['hourly_rate', 'work_hours']));
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('finance_approved_by');
            $table->dropColumn(['estimated_cost', 'finance_status', 'finance_note']);
        });
        Schema::dropIfExists('equipment_usages');
        Schema::dropIfExists('project_material_requirements');
    }
};
