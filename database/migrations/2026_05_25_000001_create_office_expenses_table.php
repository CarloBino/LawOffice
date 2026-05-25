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
        Schema::create('office_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_type')->index();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('due_date')->nullable()->index();
            $table->date('payment_date')->nullable();
            $table->string('payment_status')->default('Unpaid')->index();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_expenses');
    }
};
