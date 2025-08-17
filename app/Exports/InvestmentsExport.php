<?php

namespace App\Exports;

use App\Models\InvestmentTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvestmentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userPhone;
    protected $period;

    public function __construct(string $userPhone, string $period)
    {
        $this->userPhone = $userPhone;
        $this->period = $period;
    }

    public function collection()
    {
        $query = InvestmentTransaction::where('user_phone', $this->userPhone);

        switch ($this->period) {
            case 'bulan_ini':
                $query->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year);
                break;
            case 'tahun_ini':
                $query->whereYear('transaction_date', now()->year);
                break;
        }

        return $query->orderBy('transaction_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Transaksi',
            'Tipe',
            'Nama Aset',
            'Tipe Aset',
            'Jumlah Unit',
            'Harga per Unit',
            'Total Nilai',
        ];
    }

    public function map($investment): array
    {
        return [
            $investment->transaction_date,
            $investment->type,
            $investment->asset_name,
            $investment->asset_type,
            $investment->quantity,
            $investment->price_per_unit,
            $investment->total_amount,
        ];
    }
}
