<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'tags'
    ];

    public function tags(): HasMany {
        return $this->hasMany(Tag::class);
    }
}
