<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_books_index(): void
    {
        $response = $this->get('/books');

        $response->assertOk();
    }

    public function test_guest_can_view_book_detail(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
    }

    public function test_guest_cannot_access_book_create_page(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_book_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.create'));

        $response->assertOk();
    }

    public function test_guest_cannot_access_book_edit_page(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.edit', $book));

        $response->assertRedirect('/login');
    }

    public function test_owner_can_access_book_edit_page(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.edit', $book));

        $response->assertOk();
    }

    public function test_other_user_cannot_access_book_edit_page(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    public function test_guest_cannot_store_book(): void
    {
        $response = $this->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('books', [
            'isbn' => '1234567890123',
        ]);
    }
}
