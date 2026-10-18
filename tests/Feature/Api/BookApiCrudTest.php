<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'user_id' => $user->id,
            'title' => 'Laravelテスト',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Laravelテスト');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'Laravelテスト',
            'isbn' => '9781234567890',
        ]);
    }

    public function test_authenticated_owner_can_update_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '更新後の書籍',
            'author' => '佐藤太郎',
            'isbn' => $book->isbn,
            'published_date' => '2025-02-01',
            'genres' => [$genre->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', '更新後の書籍');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後の書籍',
        ]);
    }

    public function test_authenticated_owner_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
}
