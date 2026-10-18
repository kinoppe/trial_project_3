<?php

namespace Tests\Feature\Favorite;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->from(
            route('books.show', $book)
        )->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'お気に入りに追加しました。');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_authenticated_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->from(
            route('books.show', $book)
        )->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'お気に入りを解除しました。');

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_toggle_does_not_create_duplicate_favorites(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->actingAs($user)->post(route('favorites.toggle', $book));

        $this->assertDatabaseCount('favorites', 1);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_favorites_index_displays_only_own_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownBook = Book::factory()->create([
            'title' => '自分のお気に入り書籍',
        ]);

        $otherBook = Book::factory()->create([
            'title' => '他人のお気に入り書籍',
        ]);

        $user->favoriteBooks()->attach($ownBook->id);
        $otherUser->favoriteBooks()->attach($otherBook->id);

        $response = $this->actingAs($user)->get(
            route('favorites.index')
        );

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($ownBook) {
            return $books->total() === 1
                && $books->first()->id === $ownBook->id;
        });
    }

    public function test_guest_cannot_access_favorites(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_favorites_index_paginates_ten_books(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(11)->create();

        $user->favoriteBooks()->attach($books->pluck('id'));

        $response = $this->actingAs($user)->get(
            route('favorites.index')
        );

        $response->assertOk();

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11
                && $books->lastPage() === 2;
        });
    }
}
