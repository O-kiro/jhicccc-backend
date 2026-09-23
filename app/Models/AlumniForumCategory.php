<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'icon', 'tone', 'sort'])]
class AlumniForumCategory extends Model
{
    use HasFactory;

    public function threads(): HasMany
    {
        return $this->hasMany(AlumniForumThread::class);
    }
}
