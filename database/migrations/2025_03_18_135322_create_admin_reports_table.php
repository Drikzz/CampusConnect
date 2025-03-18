<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users');
            $table->string('type');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->morphs('subject');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_reports');
    }
};
