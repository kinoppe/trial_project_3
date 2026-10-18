<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_get_books(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'author',
                        'isbn',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_guest_can_get_book_detail(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', $book->title);
    }

    public function test_nonexistent_book_returns_404(): void
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertNotFound();
    }

    public function test_books_can_be_searched_by_keyword(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        Book::factory()->create([
            'title' => 'PHP実践',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel入門');
    }

    public function test_books_can_be_filtered_by_genre(): void
    {
        $genre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();

        $book->genres()->attach($genre->id);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->getJson(
            "/api/v1/books?genre_id={$genre->id}"
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book->id);
    }

    public function test_books_are_paginated(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 11)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_per_page_parameter_changes_page_size(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/books?per_page=3');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.per_page', 3);
    }

    public function test_invalid_per_page_returns_422(): void
    {
        $response = $this->getJson('/api/v1/books?per_page=101');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }
}
