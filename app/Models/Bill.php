<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'payment_type_id', 'period', 'amount',
    'amount_paid', 'due_on', 'paid_at', 'receipt_no',
])]
class Bill extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'amount_paid' => 'integer',
            'due_on' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function outstanding(): int
    {
        return max(0, $this->amount - $this->amount_paid);
    }

    /** Lunas ditentukan dari nominalnya, bukan kolom status tersendiri. */
    public function isPaid(): bool
    {
        return $this->outstanding() === 0;
    }

    public function scopeUnpaid(Builder $query): void
    {
        $query->whereColumn('amount_paid', '<', 'amount');
    }
}
