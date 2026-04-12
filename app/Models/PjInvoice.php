<?php

namespace App\Models;

use App\Enums\PjInvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PjInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_entry_id',
        'collaborator_id',
        'payroll_cycle_id',
        'numero_nota',
        'valor',
        'arquivo_path',
        'arquivo_nome_original',
        'data_upload',
        'data_emissao',
        'cnpj_emissor',
        'cnpj_destinatario',
        'status',
        'observacoes',
        'uploaded_by_id',
        'revisado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PjInvoiceStatus::class,
            'data_upload' => 'datetime',
            'data_emissao' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function payrollCycle(): BelongsTo
    {
        return $this->belongsTo(PayrollCycle::class);
    }

    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function payrollEntry(): BelongsTo
    {
        return $this->belongsTo(PayrollEntry::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_id');
    }
}
