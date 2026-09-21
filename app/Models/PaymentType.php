<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'amount', 'period', 'is_active'])]
class PaymentType extends Model
{
    use HasFactory;

    public const PERIODE = [
        'bulanan' => 'Bulanan',
        'semester' => 'Per Semester',
        'tahunan' => 'Tahunan',
        'sekali' => 'Sekali Bayar',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'integer', 'is_active' => 'boolean'];
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
