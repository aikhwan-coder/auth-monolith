<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Publisher;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->unique()->words(rand(2, 4), true)),
            'isbn' => $this->validIsbn13(),
            'author_id' => Author::query()->inRandomOrder()->value('id') ?? Author::create(['name' => fake()->name()])->id,
            'publisher_id' => Publisher::query()->inRandomOrder()->value('id') ?? Publisher::create(['name' => fake()->company()])->id,
            'description' => fake()->paragraph(),
            'published_year' => fake()->numberBetween(1995, 2026),
            'stock' => fake()->numberBetween(3, 15),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    /** Membuat ISBN-13 dengan check digit yang benar (lolos Value Object Isbn). */
    private function validIsbn13(): string
    {
        $base = '978'.fake()->unique()->numerify('#########'); // 12 digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return $base.((10 - ($sum % 10)) % 10);
    }
}
