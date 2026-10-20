<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->actingAs($user);
    }

    public function test_genre_index_is_displayed(): void
    {
        Genre::factory()->count(3)->create();

        $response = $this->get(route('genres.index'));

        $response->assertOk()
            ->assertViewIs('genres.index')
            ->assertViewHas('genres');
    }

    public function test_genre_detail_is_displayed(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertOk()
            ->assertViewIs('genres.show')
            ->assertViewHas('genre');
    }

    public function test_genre_can_be_created(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => 'プログラミング',
        ]);

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを登録しました。');

        $this->assertDatabaseHas('genres', [
            'name' => 'プログラミング',
        ]);
    }

    public function test_genre_can_be_updated(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->put(route('genres.update', $genre), [
            'name' => '文学',
        ]);

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを更新しました。');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '文学',
        ]);
    }

    public function test_genre_without_books_can_be_deleted(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを削除しました。');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'))
            ->assertSessionHas(
                'error',
                'このジャンルには書籍が紐づいているため削除できません。'
            );

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_genre_name_is_required(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_duplicate_genre_name_is_rejected(): void
    {
        Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->post(route('genres.store'), [
            'name' => '小説',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_genre_name_cannot_exceed_255_characters(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_genre_can_be_updated_with_same_name(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->put(route('genres.update', $genre), [
            'name' => '小説',
        ]);

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }
}
