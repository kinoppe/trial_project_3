<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => 'BookShelfテスト',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-01-01',
                'description' => 'BookShelfのテスト',
                'genres' => [$genre->id],
            ]
        );

        $book = Book::where('isbn', '9781234567890')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => 'BookShelfテスト',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_owner_can_update_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => 'BookShelf実践',
                'author' => '鈴木花子',
                'isbn' => $book->isbn,
                'published_date' => '2026-02-01',
                'description' => '更新した説明',
                'genres' => [$genre->id],
            ]
        );

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'BookShelf実践',
            'author' => '鈴木花子',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_owner_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(
            route('books.destroy', $book)
        );

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_other_user_cannot_edit_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->get(
            route('books.edit', $book)
        );

        $response->assertForbidden();
    }

    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $genre = Genre::factory()->create();

        $response = $this->actingAs($otherUser)->put(
            route('books.update', $book),
            [
                'title' => '不正な更新',
                'author' => '第三者',
                'isbn' => $book->isbn,
                'published_date' => '2025-01-01',
                'genres' => [$genre->id],
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'title' => '不正な更新',
        ]);
    }

    public function test_other_user_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(
            route('books.destroy', $book)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
