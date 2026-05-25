<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpposingParty extends Model
{
    protected $fillable = ['case_id','opposing_party_name','opposing_counsel_name','contact_number','email','address'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }
}
