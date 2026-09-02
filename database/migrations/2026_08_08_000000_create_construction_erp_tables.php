<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('nid_number')->nullable()->unique();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('location'); $table->string('division')->nullable();
            $table->decimal('total_budget', 16, 2)->default(0); $table->decimal('land_area', 12, 2)->default(0);
            $table->unsignedTinyInteger('progress_percentage')->default(0); $table->string('status')->default('planned');
            $table->string('layout_plan')->nullable(); $table->date('start_date')->nullable(); $table->date('end_date')->nullable(); $table->timestamps();
        });
        Schema::create('milestones', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('title');
            $table->date('due_date'); $table->string('status')->default('pending'); $table->unsignedTinyInteger('completion_percentage')->default(0);
            $table->string('delay_risk_level')->default('Low'); $table->timestamps();
        });
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('title');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->string('priority')->default('medium');
            $table->date('start_date')->nullable(); $table->date('end_date')->nullable(); $table->unsignedTinyInteger('progress')->default(0); $table->string('status')->default('pending'); $table->timestamps();
        });
        Schema::create('materials', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('category'); $table->decimal('current_stock', 12, 2)->default(0);
            $table->string('unit'); $table->decimal('reorder_level', 12, 2)->default(0); $table->decimal('unit_price', 14, 2)->default(0); $table->timestamps();
        });
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id(); $table->foreignId('material_id')->constrained()->cascadeOnDelete(); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); $table->decimal('quantity', 12, 2); $table->date('date'); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('contact_person')->nullable(); $table->string('phone')->nullable();
            $table->string('email')->nullable(); $table->decimal('rating', 3, 2)->default(0); $table->boolean('active_status')->default(true); $table->timestamps();
        });
        Schema::create('contractors', function (Blueprint $table) {
            $table->id(); $table->string('company_name'); $table->string('contact_person')->nullable(); $table->string('service_type');
            $table->decimal('rating', 3, 2)->default(0); $table->decimal('active_contract_value', 16, 2)->default(0); $table->timestamps();
        });
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2); $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('status')->default('pending'); $table->timestamps();
        });
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete(); $table->decimal('quantity', 12, 2); $table->decimal('total_cost', 16, 2); $table->string('status')->default('issued'); $table->timestamps();
        });
        Schema::create('supplier_quotations', function (Blueprint $table) {
            $table->id(); $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete(); $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_offered', 16, 2); $table->unsignedInteger('delivery_days'); $table->string('status')->default('pending'); $table->timestamps();
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('worker_name');
            $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->dateTime('check_in'); $table->dateTime('check_out')->nullable();
            $table->string('qr_code_hash')->index(); $table->string('status')->default('present'); $table->timestamps();
        });
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id(); $table->string('worker_name'); $table->string('role'); $table->unsignedInteger('total_days_worked')->default(0);
            $table->decimal('base_salary', 14, 2); $table->decimal('overtime_pay', 14, 2)->default(0); $table->decimal('net_pay', 14, 2);
            $table->string('payment_status')->default('pending'); $table->string('month_year'); $table->timestamps();
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('category');
            $table->decimal('amount', 16, 2); $table->string('payment_method'); $table->date('date'); $table->text('description')->nullable(); $table->timestamps();
        });
        Schema::create('daily_progress_reports', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->date('report_date');
            $table->string('weather_condition')->nullable(); $table->unsignedInteger('labor_count')->default(0); $table->text('work_summary');
            $table->foreignId('site_supervisor_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('site_photos', function (Blueprint $table) {
            $table->id(); $table->foreignId('daily_progress_report_id')->constrained()->cascadeOnDelete(); $table->string('photo_url');
            $table->string('caption')->nullable(); $table->timestamp('upload_timestamp')->useCurrent(); $table->timestamps();
        });
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('inspector_name');
            $table->string('inspection_type'); $table->string('result'); $table->text('remarks')->nullable(); $table->date('inspection_date'); $table->timestamps();
        });
        Schema::create('documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->string('category');
            $table->string('file_path'); $table->string('version')->default('1.0'); $table->string('approval_status')->default('pending'); $table->timestamps();
        });
        Schema::create('equipment', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('serial_number')->unique(); $table->string('category');
            $table->string('operational_status')->default('Active'); $table->decimal('total_hours_logged', 12, 2)->default(0);
            $table->date('last_maintenance')->nullable(); $table->date('next_maintenance_due')->nullable(); $table->timestamps();
        });
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('phone')->nullable(); $table->string('email')->nullable();
            $table->string('specialization')->nullable(); $table->unsignedInteger('active_cases_count')->default(0); $table->timestamps();
        });
        Schema::create('land_submissions', function (Blueprint $table) {
            $table->id(); $table->foreignId('landowner_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('title');
            $table->string('location'); $table->string('division'); $table->decimal('area_sqft', 14, 2); $table->decimal('asking_price', 16, 2);
            $table->foreignId('assigned_lawyer_id')->nullable()->constrained('lawyers')->nullOnDelete(); $table->string('status')->default('submitted'); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action');
            $table->string('module'); $table->text('description'); $table->string('ip_address', 45)->nullable(); $table->timestamp('timestamp')->useCurrent();
        });
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id(); $table->string('key'); $table->string('section'); $table->longText('content_en')->nullable();
            $table->longText('content_bn')->nullable(); $table->timestamps(); $table->unique(['key', 'section']);
        });
    }

    public function down(): void
    {
        foreach (['cms_pages','activity_logs','land_submissions','lawyers','equipment','documents','quality_inspections','site_photos','daily_progress_reports','expenses','payrolls','attendances','supplier_quotations','purchase_orders','purchase_requests','contractors','suppliers','inventory_transactions','materials','tasks','milestones','projects'] as $table) Schema::dropIfExists($table);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone', 'avatar', 'nid_number']));
    }
};
