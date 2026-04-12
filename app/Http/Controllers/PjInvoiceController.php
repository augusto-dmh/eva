<?php

namespace App\Http\Controllers;

use App\Enums\ContractType;
use App\Enums\PayrollCycleStatus;
use App\Enums\PjInvoiceStatus;
use App\Http\Requests\PjInvoice\StorePjInvoiceRequest;
use App\Http\Requests\PjInvoice\UpdatePjInvoiceRequest;
use App\Models\Collaborator;
use App\Models\PayrollCycle;
use App\Models\PjInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PjInvoiceController extends Controller
{
    // Admin: list all invoices for a cycle
    public function index(PayrollCycle $payrollCycle): Response
    {
        Gate::authorize('viewAny', PjInvoice::class);

        $invoices = $payrollCycle->pjInvoices()
            ->with(['collaborator', 'uploadedBy'])
            ->get();

        return Inertia::render('pj-invoices/Index', [
            'cycle' => $payrollCycle,
            'invoices' => $invoices,
        ]);
    }

    // Collaborator: view own invoices for current/active cycle
    public function selfServiceIndex(Request $request): Response
    {
        $collaborator = Collaborator::where('user_id', $request->user()->id)->firstOrFail();

        abort_unless($collaborator->tipo_contrato === ContractType::Pj, 403);

        $invoices = PjInvoice::where('collaborator_id', $collaborator->id)
            ->with(['payrollCycle'])
            ->orderByDesc('created_at')
            ->get();

        // Find current open cycle that accepts invoices
        $activeCycle = PayrollCycle::where('status', PayrollCycleStatus::AguardandoNfPj)->latest()->first();

        return Inertia::render('self-service/Invoices', [
            'invoices' => $invoices,
            'activeCycle' => $activeCycle,
            'collaborator' => $collaborator,
        ]);
    }

    // Collaborator: upload invoice
    public function store(StorePjInvoiceRequest $request): RedirectResponse
    {
        $collaborator = Collaborator::where('user_id', $request->user()->id)->firstOrFail();

        abort_unless($collaborator->tipo_contrato === ContractType::Pj, 403);

        $cycle = PayrollCycle::where('status', PayrollCycleStatus::AguardandoNfPj)->latest()->firstOrFail();

        // Store PDF to private disk
        $path = $request->file('arquivo')->store("pj-invoices/{$cycle->mes_referencia}", 'private');

        PjInvoice::create([
            'collaborator_id' => $collaborator->id,
            'payroll_cycle_id' => $cycle->id,
            'numero_nota' => $request->numero_nota,
            'valor' => $request->valor,
            'arquivo_path' => $path,
            'arquivo_nome_original' => $request->file('arquivo')->getClientOriginalName(),
            'data_upload' => now(),
            'data_emissao' => $request->data_emissao,
            'cnpj_emissor' => $request->cnpj_emissor,
            'cnpj_destinatario' => $request->cnpj_destinatario,
            'status' => PjInvoiceStatus::Pendente,
            'uploaded_by_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Nota fiscal enviada com sucesso.');
    }

    // Admin: generate signed URL for PDF download
    public function show(PjInvoice $pjInvoice): JsonResponse
    {
        Gate::authorize('view', $pjInvoice);

        // Generate signed URL valid for 30 minutes
        $url = \URL::temporarySignedRoute(
            'pj-invoices.download',
            now()->addMinutes(30),
            ['pjInvoice' => $pjInvoice->id]
        );

        return response()->json(['url' => $url]);
    }

    // Admin/Collaborator: download PDF via signed URL
    public function download(PjInvoice $pjInvoice): StreamedResponse
    {
        Gate::authorize('view', $pjInvoice);

        return Storage::disk('private')->download(
            $pjInvoice->arquivo_path,
            $pjInvoice->arquivo_nome_original
        );
    }

    // Admin: update invoice status
    public function update(UpdatePjInvoiceRequest $request, PjInvoice $pjInvoice): RedirectResponse
    {
        $pjInvoice->update([
            'status' => PjInvoiceStatus::from($request->status),
            'observacoes' => $request->observacoes,
            'revisado_por_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Nota fiscal atualizada.');
    }
}
