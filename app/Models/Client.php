<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['full_name','contact_number','email','address','client_type'];

    public function cases(): HasMany
    {
        return $this->hasMany(LegalCase::class);
    }
}
