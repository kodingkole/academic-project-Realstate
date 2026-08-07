<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('module_records', function (Blueprint $table) {
            $table->id();
            $table->string('module')->index();
            $table->string('title');
            $table->text('details')->nullable();
            $table->string('status')->default('Active');
            $table->decimal('amount', 14, 2)->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_records');
    }
};
