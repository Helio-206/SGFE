<?php

namespace App\Exports;

use App\Models\TransacaoReceita;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Mapa de Receitas via RUPE — Export Excel para reconciliação bancária.
 */
class ReceitasRupeExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles
{
    use Exportable;

    public function __construct(
        private User $user,
        private ?string $dataInicio = null,
        private ?string $dataFim = null,
    ) {}

    public function query()
    {
        $query = TransacaoReceita::query()
            ->with(['classificacaoEconomica', 'instituicao'])
            ->orderBy('data_registro', 'desc');

        if ($this->user->isGestor()) {
            $query->where('id_inst', $this->user->id_inst);
        }

        if ($this->dataInicio) {
            $query->where('data_registro', '>=', $this->dataInicio);
        }  

        if ($this->dataFim) {
            $query->where('data_registro', '<=', $this->dataFim);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID Receita',
            'Código RUPE',
            'Data Registo',
            'Fonte de Receita',
            'Classificação (cod_classe)',
            'Descrição Classificação',
            'Instituição (UO)',
            'Valor Arrecadado (AOA)',
        ];
    }

    public function map($receita): array
    {
        return [
            $receita->id_receita,
            $receita->codigo_rupe,
            $receita->data_registro->format('d/m/Y'),
            $receita->font_receita,
            $receita->classificacaoEconomica?->cod_classe ?? 'N/D',
            $receita->classificacaoEconomica?->descricao ?? 'N/D',
            ($receita->instituicao?->codigo ?? '') . ' - ' . ($receita->instituicao?->nome ?? ''),
            number_format((float) $receita->valor_arrecadado, 2, ',', '.'),
        ];
    }

    public function title(): string
    {
        return 'Mapa Receitas RUPE';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF162D50'],
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            ],
        ];
    }
}
