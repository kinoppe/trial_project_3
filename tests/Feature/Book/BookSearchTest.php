<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_can_be_searched_by_title(): void
    {
        $book = Book::factory()->create([
            'title' => 'BookShelfテスト',
        ]);

        Book::factory()->create([
            'title' => 'Laravel',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'BookShelf',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($book) {
            return $books->total() === 1
                && $books->first()->id === $book->id;
        });
    }

    public function test_books_can_be_searched_by_author(): void
    {
        $book = Book::factory()->create([
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'author' => '鈴木花子',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => '山田',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($book) {
            return $books->total() === 1
                && $books->first()->id === $book->id;
        });
    }

    public function test_books_can_be_filtered_by_genre(): void
    {
        $genre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();

        $book->genres()->attach($genre->id);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->get(route('books.index', [
            'genre' => $genre->id,
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($book) {
            return $books->total() === 1
                && $books->first()->id === $book->id;
        });
    }

    public function test_books_can_be_sorted_by_latest(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'latest',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($newBook, $oldBook) {
            return $books->pluck('id')->all() === [
                $newBook->id,
                $oldBook->id,
            ];
        });
    }

    public function test_books_can_be_sorted_by_oldest(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($oldBook, $newBook) {
            return $books->pluck('id')->all() === [
                $oldBook->id,
                $newBook->id,
            ];
        });
    }

    public function test_books_can_be_sorted_by_title(): void
    {
        $bookA = Book::factory()->create([
            'title' => 'AAA',
        ]);

        $bookB = Book::factory()->create([
            'title' => 'BBB',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'title',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($bookA, $bookB) {
            return $books->pluck('id')->all() === [
                $bookA->id,
                $bookB->id,
            ];
        });
    }

    public function test_books_can_be_sorted_by_rating(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::factory()->create();
        $lowRatedBook = Book::factory()->create();
        $unratedBook = Book::factory()->create();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
            'comment' => 'とても良い',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 2,
            'comment' => '普通',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'rating',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use (
            $highRatedBook,
            $lowRatedBook,
            $unratedBook
        ) {
            return $books->pluck('id')->all() === [
                $highRatedBook->id,
                $lowRatedBook->id,
                $unratedBook->id,
            ];
        });
    }

    public function test_invalid_sort_defaults_to_latest(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'invalid',
        ]));

        $response->assertOk();

        $response->assertViewHas('sort', 'latest');

        $response->assertViewHas('books', function ($books) use ($newBook, $oldBook) {
            return $books->pluck('id')->all() === [
                $newBook->id,
                $oldBook->id,
            ];
        });
    }

    public function test_books_can_be_searched_by_keyword_and_genre(): void
    {
        $genre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $matchedBook = Book::factory()->create([
            'title' => 'BookShelfテスト',
        ]);

        $differentGenreBook = Book::factory()->create([
            'title' => 'BookShelf実践',
        ]);

        $differentKeywordBook = Book::factory()->create([
            'title' => 'Laravel',
            'author' => '鈴木花子',
        ]);

        $matchedBook->genres()->attach($genre->id);
        $differentGenreBook->genres()->attach($otherGenre->id);
        $differentKeywordBook->genres()->attach($genre->id);

        $response = $this->get(route('books.index', [
            'keyword' => 'BookShelf',
            'genre' => $genre->id,
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($matchedBook) {
            return $books->total() === 1
                && $books->first()->id === $matchedBook->id;
        });
    }
}
