<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hearings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id')->index()->nullable();
            $table->date('hearing_date')->nullable();
            $table->time('hearing_time')->nullable();
            $table->string('court_venue')->nullable();
            $table->string('court_branch')->nullable();
            $table->string('court_jurisdiction')->nullable();
            $table->string('judge_name')->nullable();
            $table->text('hearing_purpose')->nullable();
            $table->string('hearing_status')->default('Scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearings');
    }
};
