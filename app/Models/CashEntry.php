<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['entry_date', 'direction', 'category', 'description', 'amount', 'reference'])]
class CashEntry extends Model
{
    use HasFactory;

    public const ARAH = ['masuk' => 'Pemasukan', 'keluar' => 'Pengeluaran'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['entry_date' => 'date', 'amount' => 'integer'];
    }

    /** Pengeluaran bernilai negatif saat dijumlahkan jadi saldo. */
    public function signedAmount(): int
    {
        return $this->direction === 'keluar' ? -$this->amount : $this->amount;
    }
}
