<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPayment extends Model
{
    protected $fillable = [
        'billing_id',
        'amount',
        'date_received',
        'official_receipt_number',
        'notes',
    ];

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
