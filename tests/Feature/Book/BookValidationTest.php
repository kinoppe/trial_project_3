<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasErrors('title');
    }

    public function test_author_is_required(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => 'Laravel入門',
                'isbn' => '9781234567890',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasErrors('author');
    }

    public function test_isbn_is_required(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasErrors('isbn');
    }

    public function test_duplicate_isbn_cannot_be_registered(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => '別の書籍',
                'author' => '佐藤太郎',
                'isbn' => '9781234567890',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasErrors('isbn');
    }

    public function test_genres_are_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'genres' => [],
            ]
        );

        $response->assertSessionHasErrors('genres');
    }

    public function test_invalid_image_url_is_rejected(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'image_url' => 'invalid-url',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasErrors('image_url');
    }

    public function test_invalid_published_date_is_rejected(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => 'invalid-date',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasErrors('published_date');
    }

    public function test_owner_can_update_book_with_same_isbn(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '更新した書籍',
                'author' => '山田太郎',
                'isbn' => $book->isbn,
                'published_date' => '2025-01-01',
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新した書籍',
        ]);
    }
}
