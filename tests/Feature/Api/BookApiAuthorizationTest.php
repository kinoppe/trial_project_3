<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_update_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '不正な更新',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertUnauthorized();
    }

    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'user_id' => $otherUser->id,
            'title' => '不正な更新',
            'author' => '山田太郎',
            'isbn' => $book->isbn,
            'published_date' => '2025-01-01',
            'genres' => [$genre->id],
        ]);

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

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
