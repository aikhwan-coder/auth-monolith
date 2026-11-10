<?php

namespace Modules\Catalog\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\ValueObjects\Isbn;

#[UseFactory(BookFactory::class)]
class Book extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'isbn',
        'author_id',
        'publisher_id',
        'description',
        'published_year',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    /**
     * Setiap ISBN yang disimpan divalidasi lewat Value Object Isbn
     * (format tidak valid -> InvalidArgumentException).
     */
    protected function isbn(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => (new Isbn($value))->getValue(),
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    // Many-to-Many dengan pivot added_at
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BookCategory::class, 'book_category', 'book_id', 'category_id')
            ->withPivot('added_at');
    }

    // ---------- Query scopes (reusable) ----------
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0)->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('isbn', 'like', "%{$keyword}%")
                ->orWhereHas('author', fn (Builder $a) => $a->where('name', 'like', "%{$keyword}%"));
        });
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', fn (Builder $c) => $c->where('book_categories.id', $categoryId));
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }
}
