<?php

namespace Modules\Authors\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'bio',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(\Modules\Books\Models\Book::class);
    }
}
