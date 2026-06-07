<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lawyer extends Model
{
    protected $fillable = ['user_id','full_name','contact_number','email','specialization','status'];

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) $this->full_name);

        if ($name === '') {
            return 'Atty.';
        }

        return str_starts_with(strtolower($name), 'atty.')
            ? $name
            : 'Atty. '.$name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(LegalCase::class, 'assigned_lawyer_id');
    }
}
