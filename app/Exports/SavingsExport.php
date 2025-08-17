<?php

namespace App\Exports;

use App\Models\Saving;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SavingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userPhone;

    public function __construct(string $userPhone)
    {
        $this->userPhone = $userPhone;
    }

    public function collection()
    {
        return Saving::where('user_phone', $this->userPhone)
            ->orderBy('is_emergency_fund', 'desc') // Dana Darurat di atas
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Tujuan',
            'Tipe',
            'Target',
            'Terkumpul',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    public function map($saving): array
    {
        return [
            $saving->goal_name,
            $saving->is_emergency_fund ? 'Dana Darurat' : 'Tabungan Biasa',
            $saving->target_amount,
            $saving->current_amount,
            $saving->status,
            $saving->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
