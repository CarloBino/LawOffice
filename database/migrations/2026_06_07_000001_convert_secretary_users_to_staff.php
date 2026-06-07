<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'secretary')
            ->update(['role' => 'staff']);
    }

    public function down(): void
    {
        // Secretary and staff cannot be reliably separated after consolidation.
    }
};
