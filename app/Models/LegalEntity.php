<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'apelido',
        'cnpj',
        'sindicato_patronal',
        'sindicato_trabalhadores',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(Collaborator::class);
    }
}
