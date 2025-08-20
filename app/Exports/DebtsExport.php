<?php

namespace App\Exports;

use App\Models\Debt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DebtsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userPhone;

    public function __construct(string $userPhone)
    {
        $this->userPhone = $userPhone;
    }

    public function collection()
    {
        // Mengekspor semua data hutang/piutang (tidak berdasarkan periode)
        return Debt::where('user_phone', $this->userPhone)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Dicatat',
            'Tipe',
            'Nama Orang',
            'Jumlah',
            'Deskripsi',
            'Status',
        ];
    }

    public function map($debt): array
    {
        return [
            $debt->created_at->format('Y-m-d H:i:s'),
            $debt->type,
            $debt->person_name,
            $debt->amount,
            $debt->description,
            $debt->status,
        ];
    }
}
