<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['direction', 'number', 'subject', 'correspondent', 'dated_on', 'recorded_on', 'file_url', 'note'])]
class Letter extends Model
{
    use HasFactory;

    public const ARAH = ['masuk' => 'Surat Masuk', 'keluar' => 'Surat Keluar'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['dated_on' => 'date', 'recorded_on' => 'date'];
    }
}
