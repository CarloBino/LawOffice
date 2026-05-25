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
        Schema::table('billings', function (Blueprint $table) {
            $table->decimal('acceptance_fee', 12, 2)->default(0)->after('case_id');
            $table->decimal('appearance_fee', 12, 2)->default(0)->after('acceptance_fee');
            $table->decimal('pleading_fee', 12, 2)->default(0)->after('appearance_fee');
            $table->decimal('notarial_fee', 12, 2)->default(0)->after('pleading_fee');
            $table->decimal('success_fee', 12, 2)->default(0)->after('notarial_fee');
            $table->decimal('retainer_fee', 12, 2)->default(0)->after('success_fee');
            $table->decimal('other_fees', 12, 2)->default(0)->after('retainer_fee');
            $table->date('date_received')->nullable()->after('other_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn([
                'acceptance_fee',
                'appearance_fee',
                'pleading_fee',
                'notarial_fee',
                'success_fee',
                'retainer_fee',
                'other_fees',
                'date_received',
            ]);
        });
    }
};
