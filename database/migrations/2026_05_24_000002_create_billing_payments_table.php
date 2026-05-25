<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('billings')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date_received');
            $table->string('official_receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::table('billings')
            ->where('amount_paid', '>', 0)
            ->orderBy('id')
            ->get()
            ->each(function ($billing) {
                DB::table('billing_payments')->insert([
                    'billing_id' => $billing->id,
                    'amount' => $billing->amount_paid,
                    'date_received' => $billing->date_received ?: $billing->payment_date ?: now()->toDateString(),
                    'official_receipt_number' => $billing->official_receipt_number,
                    'notes' => 'Migrated from billing amount paid.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_payments');
    }
};
