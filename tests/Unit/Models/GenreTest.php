<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class GenreTest extends TestCase
{
    public function test_genre_uses_has_factory(): void
    {
        $this->assertContains(
            HasFactory::class,
            class_uses_recursive(Genre::class)
        );
    }

    public function test_genre_fillable(): void
    {
        $genre = new Genre();

        $this->assertEquals(
            ['name'],
            $genre->getFillable()
        );
    }

    public function test_genre_has_books_relation(): void
    {
        $genre = new Genre();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $genre->books()
        );

        $this->assertEquals(
            'book_genre',
            $genre->books()->getTable()
        );

        $this->assertEquals(
            'genre_id',
            $genre->books()->getForeignPivotKeyName()
        );

        $this->assertEquals(
            'book_id',
            $genre->books()->getRelatedPivotKeyName()
        );
    }
}
