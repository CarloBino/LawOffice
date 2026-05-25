<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeExpense extends Model
{
    protected $fillable = [
        'expense_type',
        'description',
        'amount',
        'due_date',
        'payment_date',
        'payment_status',
        'receipt_number',
        'notes',
    ];
}
