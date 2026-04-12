<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyndicateBinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_entity_id',
        'syndicate_id',
    ];

    public function syndicate(): BelongsTo
    {
        return $this->belongsTo(Syndicate::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
