<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userPhone;
    protected $period;

    public function __construct(string $userPhone, string $period)
    {
        $this->userPhone = $userPhone;
        $this->period = $period;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Transaction::where('user_phone', $this->userPhone);

        switch ($this->period) {
            case 'bulan_ini':
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                break;
            case 'tahun_ini':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe',
            'Jumlah',
            'Deskripsi',
            'Kategori',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->created_at->format('Y-m-d H:i:s'),
            $transaction->type,
            $transaction->amount,
            $transaction->description,
            $transaction->category,
        ];
    }
}
