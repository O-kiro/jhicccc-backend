<?php

namespace App\Models;

use Database\Factories\ForumThreadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    /** @use HasFactory<ForumThreadFactory> */
    use HasFactory;
}
