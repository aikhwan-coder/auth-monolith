<?php

namespace Modules\Books\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'publication_year',
        'price',
        'stock',
        'author_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'publication_year' => 'integer',
        'stock' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(\Modules\Authors\Models\Author::class);
    }
}
