<?php

namespace App\Models;

use App\Enums\SyndicateType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Syndicate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo',
        'uf',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => SyndicateType::class,
        ];
    }

    public function bindings(): HasMany
    {
        return $this->hasMany(SyndicateBinding::class);
    }
}
