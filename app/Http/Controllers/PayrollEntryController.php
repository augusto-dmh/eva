<?php

namespace App\Http\Controllers;

use App\Enums\CommissionType;
use App\Http\Requests\Payroll\UpdatePayrollEntryRequest;
use App\Models\PayrollEntry;
use App\Services\Payroll\B3BusinessDayService;
use App\Services\Payroll\CommissionCalculationService;
use Illuminate\Http\RedirectResponse;

class PayrollEntryController extends Controller
{
    public function __construct(
        private CommissionCalculationService $commissionService,
        private B3BusinessDayService $b3Service,
    ) {}

    public function update(UpdatePayrollEntryRequest $request, PayrollEntry $payrollEntry): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['valor_comissao_bruta']) && $data['valor_comissao_bruta'] !== null) {
            $collaborator = $payrollEntry->collaborator;
            $tipoComissao = $collaborator->tipo_comissao;

            if ($tipoComissao === CommissionType::Closer) {
                $year = $payrollEntry->payrollCycle->ano;
                $month = $payrollEntry->payrollCycle->mes;
                $businessDays = $this->b3Service->getBusinessDaysInMonth($year, $month);
                $restDays = $this->b3Service->getRestDaysInMonth($year, $month);
                $result = $this->commissionService->calculateCloserCommission(
                    (string) $data['valor_comissao_bruta'], $businessDays, $restDays
                );
            } elseif ($tipoComissao === CommissionType::Advisor) {
                $year = $payrollEntry->payrollCycle->ano;
                $month = $payrollEntry->payrollCycle->mes;
                $b3Days = $this->b3Service->getBusinessDaysInMonth($year, $month);
                $totalDays = now()->setYear($year)->setMonth($month)->daysInMonth;
                $garantido = (string) ($collaborator->minimo_garantido ?? '0');
                $proLabore = (string) ($collaborator->salario_base ?? '0');
                $result = $this->commissionService->calculateAdvisorCommission(
                    (string) $data['valor_comissao_bruta'], $garantido, $proLabore, $b3Days, $totalDays
                );
            } else {
                $result = [
                    'comissao_bruta' => $data['valor_comissao_bruta'],
                    'valor_dsr' => 0,
                    'valor_comissao_total' => $data['valor_comissao_bruta'],
                ];
            }

            $data['valor_comissao_bruta'] = $result['comissao_bruta'];
            $data['valor_dsr'] = $result['valor_dsr'];
            $data['valor_comissao_total'] = $result['valor_comissao_total'];
        }

        $payrollEntry->update($data);

        return back()->with('success', 'Comissão atualizada com sucesso.');
    }
}
