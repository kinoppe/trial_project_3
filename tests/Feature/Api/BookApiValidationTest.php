<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_title_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_book_author_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Laravel入門',
            'isbn' => '9781234567890',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('author');
    }

    public function test_duplicate_isbn_is_rejected(): void
    {
        $user = User::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => '重複する書籍',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('isbn');
    }

    public function test_invalid_isbn_length_is_rejected(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '12345678901234',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('isbn');
    }
}
