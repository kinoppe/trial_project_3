<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_index_displays_books(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
    }

    public function test_books_index_displays_multiple_books(): void
    {
        Book::factory()->create([
            'title' => 'PHP入門',
        ]);

        Book::factory()->create([
            'title' => 'Laravel実践',
        ]);

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertSee('PHP入門');
        $response->assertSee('Laravel実践');
    }

    public function test_books_index_is_empty_when_no_books_exist(): void
    {
        $response = $this->get('/books');

        $response->assertStatus(200);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_books_index_paginates_ten_books(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11
                && $books->lastPage() === 2;
        });
    }

    public function test_book_detail_displays_book_information(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel完全ガイド',
            'author' => '山田太郎',
        ]);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertSee('Laravel完全ガイド');
        $response->assertSee('山田太郎');
    }

    public function test_nonexistent_book_returns_404(): void
    {
        $response = $this->get('/books/999999');

        $response->assertStatus(404);
    }
}
