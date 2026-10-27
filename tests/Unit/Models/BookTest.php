<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class BookTest extends TestCase
{
    public function test_book_has_genres_relation(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->genres()
        );
    }

    public function test_book_has_reviews_relation(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            HasMany::class,
            $book->reviews()
        );
    }

    public function test_book_has_favorited_users_relation(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->favoritedUsers()
        );
    }

    public function test_book_belongs_to_user(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->user()
        );
    }

    public function test_book_has_reading_plans_relation(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            HasMany::class,
            $book->readingPlans()
        );
    }

    public function test_book_has_creator_relation(): void
    {
        $book = new Book();

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->creator()
        );
    }

    public function test_published_date_is_cast_to_date(): void
    {
        $book = new Book();

        $this->assertEquals(
            'date',
            $book->getCasts()['published_date']
        );
    }
}