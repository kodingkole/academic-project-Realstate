<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('land_submissions', function (Blueprint $table) {
            $table->string('code')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('landowner_name')->nullable(); $table->string('phone')->nullable(); $table->string('district')->nullable();
            $table->decimal('katha_size', 8, 2)->nullable(); $table->unsignedInteger('road_width')->nullable(); $table->string('landmark')->nullable();
            $table->text('description')->nullable(); $table->string('nid_number')->nullable(); $table->string('deed_path')->nullable(); $table->string('nid_path')->nullable();
            $table->string('stage')->default('Submitted'); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->text('rejection_reason')->nullable(); $table->timestamp('submitted_at')->nullable();
        });
        Schema::create('jv_agreements', function (Blueprint $table) {
            $table->id(); $table->foreignId('land_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('landowner_share_pct', 5, 2); $table->decimal('developer_share_pct', 5, 2);
            $table->json('allocated_flats_json')->nullable(); $table->text('terms'); $table->string('status')->default('draft');
            $table->timestamp('signed_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('jv_agreements');
        Schema::table('land_submissions', fn (Blueprint $table) => $table->dropColumn(['code','user_id','landowner_name','phone','district','katha_size','road_width','landmark','description','nid_number','deed_path','nid_path','stage','project_id','rejection_reason','submitted_at']));
    }
};
