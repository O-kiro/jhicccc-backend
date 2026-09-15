<?php

namespace App\Models;

use Database\Factories\ForumCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    /** @use HasFactory<ForumCategoryFactory> */
    use HasFactory;
}
