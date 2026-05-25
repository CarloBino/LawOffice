<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalCase extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'case_number', 'case_title', 'case_type', 'case_status', 'client_id', 'assigned_lawyer_id', 'date_filed', 'description', 'priority_level'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedLawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'assigned_lawyer_id');
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(Hearing::class, 'case_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CaseAction::class, 'case_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class, 'case_id');
    }

    public function opposingParties(): HasMany
    {
        return $this->hasMany(OpposingParty::class, 'case_id');
    }
}
