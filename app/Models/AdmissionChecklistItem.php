<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_checklist_id',
        'descricao',
        'obrigatorio',
        'confirmado',
        'confirmado_em',
        'confirmado_por_id',
        'documento_path',
        'observacoes',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'obrigatorio' => 'boolean',
            'confirmado' => 'boolean',
            'confirmado_em' => 'datetime',
        ];
    }

    public function admissionChecklist(): BelongsTo
    {
        return $this->belongsTo(AdmissionChecklist::class);
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por_id');
    }
}
