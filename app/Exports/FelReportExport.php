<?php

namespace App\Exports;

use App\Models\FelDocument;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FelReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected string $from,
        protected string $to,
        protected ?string $status = null,
    ) {
    }

    public function collection(): Collection
    {
        $query = FelDocument::query()
            ->with(['sale.user'])
            ->whereBetween('issued_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);

        if ($this->status) {
            $query->where('fel_status', $this->status);
        }

        return $query
            ->orderByDesc('issued_at')
            ->get()
            ->map(function ($doc) {
                return [
                    'fecha' => optional($doc->issued_at)->format('Y-m-d H:i:s'),
                    'venta' => $doc->sale_id,
                    'uuid' => $doc->uuid,
                    'serie' => $doc->series,
                    'numero' => $doc->number,
                    'cliente' => $doc->receiver_name ?? 'CONSUMIDOR FINAL',
                    'nit_cui' => $doc->receiver_taxid ?? 'CF',
                    'total' => (float) ($doc->total_amount_q ?? $doc->sale->total_amount_q ?? 0),
                    'estado' => $doc->fel_status,
                    'usuario' => $doc->sale->user->name ?? '—',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Venta #',
            'UUID',
            'Serie',
            'Número',
            'Cliente',
            'NIT/CUI',
            'Total',
            'Estado FEL',
            'Usuario',
        ];
    }
}