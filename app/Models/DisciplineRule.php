<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'title', 'description', 'kind', 'category', 'points', 'is_active'])]
class DisciplineRule extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['points' => 'integer', 'is_active' => 'boolean'];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DisciplineRecord::class);
    }

    /** Pelanggaran menambah poin; penghargaan menguranginya. */
    public function signedPoints(): int
    {
        return $this->kind === 'penghargaan' ? -$this->points : $this->points;
    }
}
