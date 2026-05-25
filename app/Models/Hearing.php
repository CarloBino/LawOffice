<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hearing extends Model
{
    protected $fillable = ['case_id','hearing_date','hearing_time','court_venue','court_branch','court_jurisdiction','judge_name','hearing_purpose','hearing_status'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }
}
