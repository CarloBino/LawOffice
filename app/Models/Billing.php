<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    protected $fillable = [
        'case_id',
        'acceptance_fee',
        'appearance_fee',
        'pleading_fee',
        'notarial_fee',
        'success_fee',
        'retainer_fee',
        'other_fees',
        'professional_fee',
        'filing_fee',
        'other_expenses',
        'total_amount',
        'amount_paid',
        'balance',
        'payment_status',
        'payment_date',
        'official_receipt_number',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }

    public function recalculatePaymentTotals(): void
    {
        $amountPaid = (float) $this->payments()->sum('amount');
        $balance = max((float) $this->total_amount - $amountPaid, 0);

        $this->forceFill([
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'payment_status' => $balance <= 0
                ? 'Paid'
                : ($amountPaid > 0 ? 'Partial' : 'Unpaid'),
            'payment_date' => $this->payments()->max('date_received'),
            'official_receipt_number' => optional($this->payments()->latest('date_received')->latest('id')->first())->official_receipt_number,
        ])->save();
    }
}
